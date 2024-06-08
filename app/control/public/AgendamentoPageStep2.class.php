<?php
/**
 * AgendamentoPageStep2
 *
 * @version    7.6
 * @package    control
 * @subpackage public
 * @author     Marcos David Souza Ramos
 */
class AgendamentoPageStep2 extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private $deleteButton;

    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Agenda');
        $this->form->setFormTitle('SELECIONE SEUS PROCEDIMENTOS');
        $this->form->setFieldSizes('100%');

        $pagestep = new TPageStep;
        $pagestep->addItem('Início');
        $pagestep->addItem('Procedimentos');
        $pagestep->addItem('Data e Horário');
        $pagestep->addItem('Confirmação');
        $pagestep->select('Procedimentos');

        // Criar os campos do formulário
        //$id = new TEntry('id');
        $area_id = new TDBCombo('area_id', 'app', 'Area', 'id', 'titulo', 'titulo');
        $area_id->setChangeAction(new TAction(array($this, 'onChangeArea')));
        $area_id->setDefaultOption(false);
        //self::onChangeArea(['area_id' => "1"]);

        /*$categoria_id = new TDBCombo('categoria_id', 'app', 'Categoria', 'id', 'titulo', 'titulo');
        $categoria_id->setDefaultOption(false);*/

        $tempo    = new TEntry('tempo');
        $tempo->placeholder = '0h00';
        
        $total    = new TEntry('total');
        $total->placeholder = 'R$ 0,00';

        $multi    = new TMultiEntry('multi');
        $multi->setValue(array('aaa','bbb'));
        

        // Adicionar os campos ao formulário
        
        $row = $this->form->addFields(
            [ new TLabel('Área'), $area_id ],
            [ new TLabel('Tempo de Serviço'), $tempo ],
            [ new TLabel('Valor Total'), $total ],
        );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        /*$btn = $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        $btn = $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default';*/

        $action = new TAction(array($this, 'nextPage'));
        $next_page = new TActionLink('Próximo', $action, 'next', null, null, 'far:arrow-alt-circle-right green');
        $next_page->addStyleClass('btn btn-default btn-sm');

        
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->disableDefaultClick();
        //$this->datagrid->setActionSide('right');
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'ID', 'center');
        $column_titulo = new TDataGridColumn('titulo', 'PROCEDIMENTO', 'left');
        $column_tempo = new TDataGridColumn('tempo', 'TEMPO (minutos)', 'center');
        $column_valor = new TDataGridColumn('valor', 'VALOR', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id)->setVisibility(false);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_tempo);
        $this->datagrid->addColumn($column_valor);


        $column_id->setTransformer( function($value, $object, $row) {
            $array = TSession::getValue('procedimentos-selecionados');
            if(is_array($array) and !empty($array)){
                if(in_array($value, array_keys($array))){
                    $row->style= 'text-shadow:none; color:#000000; background-color: #aae499;';
                }
            }
            
            return $value;
        });

        $column_valor->setTransformer( function($value, $object, $row) {
            return AppHelper::toMonetary($value);
        });


        $action_edit = new TDataGridAction([$this, 'onSelect']);
        $action_edit->setButtonClass('btn btn-sm btn-primary');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:check #020c44');
        $action_edit->setFields(['id', 'tempo', 'valor']);
        $this->datagrid->addAction($action_edit);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        /*$this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());*/
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add( $pagestep );
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $next_page));
        
        parent::add($container);
    }

    public static function onChangeArea($param)
    {
        TSession::setValue('procedimentos-selecionados',  NULL);

        $data = new stdClass;
        if(isset($param['area_id']) and $param['area_id']){
            $data->area_id = $param['area_id'];
            
            $filter = new TFilter('area_id', '=', $data->area_id); 
            TSession::setValue(__CLASS__.'_filter_area_id',   $filter); 
        }

        TSession::setValue(__CLASS__.'_filter_data', $data);
        AdiantiCoreApplication::loadPage('AgendamentoPageStep2', 'onReload');
    }


    public function nextPage()
    {
        AdiantiCoreApplication::loadPage('AgendamentoPageStep3');
    }


    public function onClear($param)
    {
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_area_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_categoria_id',   NULL);

        //Limpar formulário depois...

        $this->onReload($param);
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        
        $data = $this->form->getData();
    
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_area_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_categoria_id',   NULL);

        if (isset($data->categoria_id) AND ($data->categoria_id)) {
            $filter = new TFilter('categoria_id', '=', $data->categoria_id); 
            TSession::setValue(__CLASS__.'_filter_categoria_id',   $filter); 
        }        
        
        $this->form->setData($data);
        
        
        TSession::setValue(__CLASS__.'_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }




    public function onSelect($param)
    {

        if(isset($param['key']) and $param['key']){

            $key = $param['key'];
            $tempo = $param['tempo'];
            $valor = $param['valor'];

            $procedimentos = TSession::getValue('procedimentos-selecionados'); 
            if(is_array($procedimentos)){
                if(in_array($key, array_keys($procedimentos))){
                    unset($procedimentos[$key]);
                }else{
                    $procedimentos[$key] = array('tempo' => $tempo, 'valor' => $valor);
                }
            }else{
                $procedimentos = array();
                $procedimentos[$key] = array('tempo' => $tempo, 'valor' => $valor);
            }

            TSession::setValue('procedimentos-selecionados',  $procedimentos); 

            $this->onReload($param);
        }
    }

    public function onUpdateForm()
    {
        $procedimentos = TSession::getValue('procedimentos-selecionados'); 
        if(is_array($procedimentos)){
            $std = new stdClass;
            $std->tempo = 0;
            $std->total = 0.0;

            foreach ($procedimentos as $procedimento) {
                $std->tempo += (int)$procedimento['tempo'];
                $std->total += (float)$procedimento['valor'];
            }

            $std->tempo = AppHelper::toHour($std->tempo);
            $std->total = AppHelper::toMonetary($std->total);

            TForm::sendData('form_search_Agenda', $std);
        }
    }

    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try{
            TTransaction::open('app');
            
            $repository = new TRepository('ViewProcedimento');
            //$limit = 20;
            
            $criteria = new TCriteria;
            
            if (empty($param['order']))
            {
                $param['order'] = 'titulo';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); 
            //$criteria->setProperty('limit', $limit);
            
            if (TSession::getValue(__CLASS__.'_filter_area_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_area_id')); 
            }else{
                $criteria->add(new TFilter('area_id', '=', '1'));
            }

            $criteria->add(new TFilter('ativo', '=', "Y"));
            $criteria->add(new TFilter('deleted_at', 'is', NULL)); 


            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects){
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }
            
            $criteria->resetProperties();
            
            TTransaction::close();
            $this->loaded = true;

            $this->onUpdateForm();
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
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0){
                $this->onReload( func_get_arg(0) );
            }else{
                $this->onReload();
            }
        }
        parent::show();
    }
}
