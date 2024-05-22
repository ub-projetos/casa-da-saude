<?php
/**
 * PacienteList Listing
 * @author  <your name here>
 */
class PacienteList extends TPage
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
        $this->form = new BootstrapFormBuilder('form_search_Paciente');
        $this->form->setFormTitle('LISTAGEM DE PACIENTES');
        $this->form->setFieldSizes('100%');

        // Criar os campos do formulário
        //$id = new TEntry('id');
        $nome = new TEntry('nome');
        $nome->forceUpperCase();
        $nome->placeholder = "Digite o nome do paciente";

        $cpf = new TEntry('cpf');
        $cpf->setMask('999.999.999-99', true);

        $celular = new TEntry('celular');
        $celular->setMask('(99)99999-9999', true);
        

        // Adicionar os campos ao formulário
        
        $row = $this->form->addFields(
            [ new TLabel('Pesquisa'), $nome ],
            [ new TLabel('CPF'), $cpf ],
            [ new TLabel('Celular'), $celular ],
        );
        $row->layout = ['col-sm-6', 'col-sm-3', 'col-sm-3'];

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addActionLink(('Novo Paciente'), new TAction(
            ['PacienteForm', 'onEdit']), 'fa:plus'
        );
        $btn->class = 'btn btn-sm btn-primary';

        $btn = $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default right';

        $btn = $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-default right';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->disableDefaultClick();
        //$this->datagrid->setActionSide('right');
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'ID', 'center');
        $column_nome = new TDataGridColumn('nome', 'NOME&nbsp;PACIENTE', 'left');
        $column_cpf = new TDataGridColumn('cpf', 'CPF/&nbsp;CNPJ', 'center');
        $column_celular = new TDataGridColumn('celular', 'CELULAR', 'center');
        $column_email = new TDataGridColumn('email', 'E-MAIL', 'center');
       // $column_active = new TDataGridColumn('active', 'ATIVO', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id)->setVisibility(false);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_cpf);
        $this->datagrid->addColumn($column_celular);
        $this->datagrid->addColumn($column_email);
        //$this->datagrid->addColumn($column_active);

        $column_nome->setTransformer(function ($value, $object, $row) {
            return $value;
        });

        $column_celular->setTransformer( function($value, $object, $row) {
            return AppHelper::formatPhone($value);
        });

        $column_cpf->setTransformer( function($value, $object, $row) {
            return AppHelper::formatCpfCnpj($value);
        });
        
        
        $action_edit = new TDataGridAction(['PacienteForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-sm btn-primary');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit #020c44');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        //$action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }


    public function onClear($param)
    {
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_cpf',   NULL);
        TSession::setValue(__CLASS__.'_filter_celular',   NULL);

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
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_cpf',   NULL);
        TSession::setValue(__CLASS__.'_filter_celular',   NULL);


        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); 
            TSession::setValue(__CLASS__.'_filter_nome',   $filter);
        }

        if (isset($data->cpf) and ($data->cpf)) {
            $filter = new TFilter('cpf', '=', $data->cpf); 
            TSession::setValue(__CLASS__.'_filter_cpf', $filter);
        }

        if (isset($data->celular) AND ($data->celular)) {
            $filter = new TFilter('celular', '=', $data->celular); 
            TSession::setValue(__CLASS__.'_filter_celular',   $filter); 
        }        
        
        $this->form->setData($data);
        
        
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try{
            TTransaction::open('app');
            
            $repository = new TRepository('Paciente');
            $limit = 15;
            
            $criteria = new TCriteria;
            
            if (empty($param['order']))
            {
                $param['order'] = 'nome';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); 
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__.'_filter_nome')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_nome')); 
            }

            if (TSession::getValue(__CLASS__.'_filter_cpf')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_cpf')); 
            }

            if (TSession::getValue(__CLASS__.'_filter_celular')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_celular')); 
            }

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
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); 
            $this->pageNavigation->setProperties($param); 
            $this->pageNavigation->setLimit($limit); 
            
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
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
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
