<?php
/**
 * AgendamentoPageStep2Backup
 *
 * @version    7.6
 * @package    control
 * @subpackage public
 * @author     Marcos David Souza Ramos
 */
class AgendamentoPageStep2Backup extends TPage
{
    private $box;
    
    public function __construct()
    {
        parent::__construct();
        
        // creates one datagrid
        $this->box = new THBox;
        $this->box->style = 'display:flex; flex-direction: row;';
        
       
        
        $pagestep = new TPageStep;
        $pagestep->addItem('Welcome');
        $pagestep->addItem('Selection');
        $pagestep->addItem('Complete information');
        $pagestep->addItem('Confirmation');
        $pagestep->select('Selection');
        
        $back_action = new TAction(array($this, 'backPage'));
        $back = new TActionLink('Back', $back_action, 'black', null, null, 'far:arrow-alt-circle-left red');
        $back->addStyleClass('btn btn-default btn-sm');
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', 'AgendamentoPageStep2'));
        $vbox->add( $pagestep );
        $vbox->add( TPanelGroup::pack('', $this->box, $back ) );
        
        // wrap the page content
        parent::add($vbox);
    }

    function backPage()
    {
        AdiantiCoreApplication::loadPage('AgendamentoPage');
    }
    
    /**
     * Load the data into the datagrid
     */
    function onReload()
    {
        try{
            TTransaction::open('app');
            
            $criteria = new TCriteria;
            $props['order'] = 'titulo';
            $props['direction'] = 'asc';
            $criteria->setProperties($props); 

            $objects = Area::getObjects($criteria);
            
            //$this->datagrid->clear();
      
            foreach ($objects as $object)
            {
                $action = new TAction(array($this, 'onSelect'), [
                    'id' => $object->id,
                    'titulo' => $object->titulo,
                ]);
                $back = new TActionLink($object->titulo, $action, "$object->titulo-$object->id", null, null, '');
                $back->addStyleClass('btn btn-default btn-sm');
                
                $this->box->add($back);
            }
            
            
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * method onView()
     * Executed when the user clicks at the view button
     */
    function onSelect($param)
    {
        TSession::setValue('registration_course', [
            'area_id' => $param['id'],
            'area_titulo' => $param['titulo']
        ]);
        
        AdiantiCoreApplication::loadPage('AgendamentoPageStep3');
    }
    
    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
}
