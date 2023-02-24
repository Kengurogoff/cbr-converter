<?php

namespace App\Controller;

use App\Form\Type\ConverterType;
use App\Service\CurrencyLoader;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
            'currency_codes' => $currencyCodes
        ]);

        return $this->render('index.html.twig',[
            'converterForm' => $form->createView()
        ]);
    }

}
