<?php
/**
 * AgendamentoPageStep3
 *
 * @version    7.6
 * @package    control
 * @subpackage public
 * @author     Marcos David Souza Ramos
 */
class AgendamentoPageStep3 extends TPage
{
    private $form_search;
    private $loaded;
    private $box;

    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();

        $array = TSession::getValue('procedimentos-selecionados');
        if(empty($array)){
            TToast::show('error', 'Por favor! Selecione um procedimento.', 'top right', 'fa:exclamation-triangle');
            $this->backPage();
        }
        
        // creates the form
        $this->form_search = new BootstrapFormBuilder('form_search_Agenda');
        $this->form_search->setFormTitle('AGENDA');
        $this->form_search->setFieldSizes('100%');

        $pagestep = new TPageStep;
        $pagestep->addItem('Início');
        $pagestep->addItem('Procedimentos');
        $pagestep->addItem('Data e Horário');
        $pagestep->addItem('Confirmação');
        $pagestep->select('Data e Horário');

        // Criar os campos do formulário
        //$id = new TEntry('id');
        $profissional_id = new TDBCombo('profissional_id', 'app', 'ViewProfissional', 'id', 'name', 'name');
        $profissional_id->setDefaultOption('Selecione');
        $profissional_id->setChangeAction(new TAction(array($this, 'onChangeAction')));

        $data_agenda = new TDate('data_agenda');
        $data_agenda->setExitAction(new TAction(array($this, 'onChangeAction')));
        $data_agenda->setMask("dd/mm/yyyy");
        $data_agenda->setDatabaseMask("yyyy-mm-dd");
        $data_agenda->setValue(date('d/m/Y'));
        $data_agenda->placeholder = "dd/mm/aaaa";

        // Adicionar os campos ao formulário
        
        $row = $this->form_search->addFields(
            [ new TLabel('Data'), $data_agenda ],
            [ new TLabel('Profissional'), $profissional_id ],
        );
        $row->layout = ['col-sm-3', 'col-sm-4', 'col-sm-4'];
        
        // keep the form filled during navigation with session data
        $this->form_search->setData( TSession::getValue(__CLASS__ . '_filter_data') );

        $this->box = new THBox;
        $this->box->style = 'display:flex; flex-direction: row;';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add( $pagestep );
        $container->add($this->form_search);
        $container->add(TPanelGroup::pack('', $this->box));
        
        parent::add($container);
    }

    public static function onChangeAction($param)
    {
        TSession::setValue(__CLASS__.'_filter_data', NULL);

        $data = new stdClass;
        if(isset($param['data_agenda']) and $param['data_agenda']){
            $data->data_agenda = $param['data_agenda'];
            
            $filter = new TFilter('data_agenda', '=', AppHelper::toDateUS($data->data_agenda)); 
            TSession::setValue(__CLASS__.'_filter_data_agenda',   $filter); 
        }

        if(isset($param['profissional_id']) and $param['profissional_id']){
            $data->profissional_id = $param['profissional_id'];
            
            $filter = new TFilter('profissional_id', '=', $param['profissional_id']); 
            TSession::setValue(__CLASS__.'_filter_profissional_id',   $filter); 
        }
    
        TSession::setValue(__CLASS__.'_filter_data', $data);
        AdiantiCoreApplication::loadPage('AgendamentoPageStep3', 'onReload');
    }


    public function backPage()
    {
        AdiantiCoreApplication::loadPage('AgendamentoPageStep2');
    }


    public function onClear($param)
    {
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_profissional_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_agenda',   NULL);

        //Limpar formulário depois...

        $this->onReload($param);
    }

    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        
        try{
            TTransaction::open('app');
            
            $repository = new TRepository('Agenda');
            $limit = 20;
            
            $criteria = new TCriteria;
            
            if (empty($param['order']))
            {
                $param['order'] = 'data_agenda';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); 
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__.'_filter_profissional_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_profissional_id')); 
            }

            if (TSession::getValue(__CLASS__.'_filter_data_agenda')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_agenda')); 
            } else {
                $criteria->add($filter = new TFilter('data_agenda', '=', date('Y-m-d'))); 
            }            

            $criteria->add(new TFilter('ativa', '=', "Y"));
            $criteria->add(new TFilter('deleted_at', 'is', NULL)); 


            $objects = $repository->load($criteria, FALSE);

            if ($objects){
                foreach ($objects as $object)
                {
            
                }
            }
            
            $criteria->resetProperties();
            
            TTransaction::close();
            $this->loaded = true;

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
