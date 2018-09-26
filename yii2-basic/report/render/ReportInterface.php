<?php

namespace app\report\render;

/*Интерфейс для показа отчёта в списке*/
interface ReportInterface
{
    public function getName();//имя
    public function getAlias();//алиас
    public function getHtmlParams();//параметры выгрузки
}