<?php
/**
 * TestePage
 *
 * @version    7.6
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class TestePage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        //parent::add(new TLabel('Teste Page'));

        TTransaction::open('app');

        $procedimento = new Procedimento(1);
        $ids = $procedimento->getProfissionalIds();

        var_dump($ids);

        /*foreach ($objects as $object)
        {
            echo $object->name;
        }*/

        TTransaction::close();
        
    }
}
