<?php
/**
 * AgendamentoPage
 *
 * @version    7.6
 * @package    control
 * @subpackage public
 * @author     Marcos David Souza Ramos
 */
class AgendamentoPage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        try
        {
            // create the HTML Renderer
            $this->html = new THtmlRenderer('app/resources/welcome.html');
            $this->html->enableSection('main');
            
            $pagestep = new TPageStep;
            $pagestep->addItem('Início');
            $pagestep->addItem('Procedimentos');
            $pagestep->addItem('Data e Horário');
            $pagestep->addItem('Confirmação');
            $pagestep->select('Início');

            //$action = new TAction(array($this, 'nextPage'));
            //$next_page = new TActionLink('Back', $action, 'next', null, null, 'far:arrow-alt-circle-right green');
            //$next_page->addStyleClass('btn btn-default btn-sm');
            
            // wrap the page content using vertical box
            $vbox = new TVBox;
            $vbox->style = 'width: 100%';
            //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
            $vbox->add( $pagestep );
            $vbox->add( TPanelGroup::pack('', $this->html) );
            parent::add($vbox);
        }
        catch (Exception $e){
            new TMessage('error', $e->getMessage());
        }
    }

    /*function nextPage()
    {
        AdiantiCoreApplication::loadPage('AgendamentoPage');
    }*/
}
