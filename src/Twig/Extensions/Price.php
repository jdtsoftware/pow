<?php

namespace JDT\Pow\Twig\Extensions;

class Price extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'format')),
        );
    }

    public function format($number)
    {
        setlocale(LC_MONETARY, \Config::get('pow.locale'));
        return money_format(\Config::get('pow.money_format'), $number);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'price';
    }
}