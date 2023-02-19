<?php

namespace App\Service;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;

class CurrencyLoader
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Gets existing currencies from DB.
     * Otherwise, loads them from CBR site.
     *
     * @param \DateTime $date
     * @return array|null
     */
    public function loadCurrencies(\DateTime $date): ?array
    {
        $currencyRepository = $this->em->getRepository(Currency::class);
        $currencies = $currencyRepository->findByDate($date);

        if (empty($currencies)) {
            $currencies = $this->loadXML($date);

            if ($currencies != null) {
                $this->saveCurrenciesToDB($currencies, $date);
                $currencies = $currencyRepository->findByDate($date);
            }
        }

        return $currencies;
    }

    private function loadXML(\DateTime $date): ?array
    {
        $date = $date->format('d/m/Y');
        $pathToXML = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date";

        $currenciesXML = new DOMDocument("1.0", "cp1251");
        $currenciesXML->loadXML(file_get_contents($pathToXML));

        $currencies = $currenciesXML->documentElement->getElementsByTagName('Valute');
        foreach ($currencies as $currency) {
            $code = $currency->getElementsByTagName('CharCode')->item(0)->nodeValue;
            $ratio = $currency->getElementsByTagName('Nominal')->item(0)->nodeValue;
            $value = $currency->getElementsByTagName('Value')->item(0)->nodeValue;

            $result[$code] = [
                'ratio' => (float)$ratio,
                'value' => (float)str_replace(',', '.', $value)
            ];
        }

        return $result ?? null;
    }

    private function saveCurrenciesToDB(array $currencies, \DateTime $date)
    {
        $obj = new Currency();
        $obj->setCode('RUB')
            ->setRatio(1)
            ->setValue(1)
            ->setDate($date);

        $this->em->persist($obj);

        foreach ($currencies as $code => $currency) {
            $obj = new Currency();
            $obj->setCode($code)
                ->setRatio($currency['ratio'])
                ->setValue($currency['value'])
                ->setDate($date);

            $this->em->persist($obj);
        }

        $this->em->flush();
    }

}
