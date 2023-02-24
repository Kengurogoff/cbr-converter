<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Form\Type\ConverterType;
use App\Service\{CurrencyConverter, CurrencyLoader};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * @Route("/", name="index_page")
     */
    public function index(): Response
    {
        $em = $this->doctrine->getManager();

        // get currencies for the current date from DB
        $date = new \DateTime();
        $loader = new CurrencyLoader($em);
        $currencies = $loader->loadCurrencies($date);

        // default form data
        $currencyCodes = [];
        foreach ($currencies as $currency) {
            $currencyCodes[$currency['code']] = $currency['code'];
        }
        $defaultData = [
            'amount' => 1,
            'from' => 'USD',
            'to' => 'RUB',
        ];

        $form = $this->createForm(ConverterType::class, $defaultData, [
            'currency_codes' => $currencyCodes,
            'action' => $this->generateUrl('converter'),
            'method' => 'GET'
        ]);

        // convert default currencies
        $converter = $this->forward('App\Controller\CurrencyController::converter', $defaultData);

        return $this->render('index.html.twig', [
            'converterForm' => $form->createView(),
            'result' => $converter->getContent()
        ]);
    }

    /**
     * @Route("/convert", name="converter")
     */
    public function converter(Request $request): Response
    {
        $amount = $request->get('amount');
        $from = $request->get('from');
        $to = $request->get('to');

        if (!isset($amount, $from, $to)) {
            return new Response('Missing params', Response::HTTP_BAD_REQUEST);
        }

        $em = $this->doctrine->getManager();
        $currencyRepository = $em->getRepository(Currency::class);

        $currencyFrom = $currencyRepository->findOneBy([
            'date' => new \DateTime(),
            'code' => $from
        ]);

        $currencyTo = $currencyRepository->findOneBy([
            'date' => new \DateTime(),
            'code' => $to
        ]);

        if (!isset($currencyFrom) && !isset($currencyTo)) {
            return new Response('Currency not exists', Response::HTTP_NOT_FOUND);
        }

        $amount = (float)$amount;
        $result = CurrencyConverter::convert($currencyFrom, $currencyTo, $amount);

        return new Response($result);
    }

}
