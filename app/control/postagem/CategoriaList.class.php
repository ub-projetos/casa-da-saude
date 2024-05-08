<?php
/**
 * CategoriaList Listing
 * @author  Marcos David Souza Ramos
 */
class CategoriaList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Categoria');
        $this->form->setFormTitle('LISTAGEM DE CATEGORIAS');
        $this->form->setFieldSizes('100%');

        $nome = new TEntry('nome');
        $nome->placeholder = "Nome da categoria";
        $active = new TCombo('active');
        $active->setDefaultOption("Selecione");
        $active->addItems(PostagemCategoria::list_items());

        $row = $this->form->addFields(
            [ new TLabel('Pesquisa'), $nome ],
            [ new TLabel('Ativo'), $active ],
        );
        $row->layout = ['col-sm-10', 'col-sm-2'];
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addActionLink(('Nova Categoria'), new TAction(['CategoriaForm', 'onEdit']), 'fa:plus');
        $btn->class = 'btn btn-sm btn-primary';

        $btn = $this->form->addAction("Pesquisar", new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-default';

        $btn = $this->form->addAction("Limpar", new TAction([$this, 'onClear']), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default ';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        $this->datagrid->setActionSide('right');
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'ID', 'center');
        $column_titulo = new TDataGridColumn('titulo', 'TÍTULO', 'left');
        $column_created_at = new TDataGridColumn('created_at', 'DATA CADASTRO', 'center');
        $column_ativo = new TDataGridColumn('ativo', 'ATIVO', 'center');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_created_at);
        $this->datagrid->addColumn($column_ativo);

        $column_ativo->setTransformer( function($value, $object, $row) {
            $class = ($value=='Y') ? 'success' : 'danger';
            $label = ($value=='Y') ? _t('Yes') : _t('No');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });

        $column_created_at->setTransformer( function($value, $object, $row) {
            if(!empty($value)){
                $date = new DateTime($value);
                return $date->format('d/m/Y');
            }
        });

        $action1 = new TDataGridAction(['CategoriaForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }  
    

    public function onClear($param)
    {       
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_parent_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_ativo',   NULL);

        Convert::toCleanForm($this->form);

        $this->onReload($param);
    }

    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_nome',   NULL);
        TSession::setValue(__CLASS__.'_filter_ativo',   NULL);

        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'like', "%{$data->nome}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_nome',   $filter); // stores the filter in the session
        }

        if (isset($data->ativo) AND ($data->ativo)) {
            $filter = new TFilter('ativo', 'like', "%{$data->ativo}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_ativo',   $filter); // stores the filter in the session
        }

        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
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
        try
        {
            // open a transaction with database 'app'
            TTransaction::open('app');
            
            // creates a repository for Categoria
            $repository = new TRepository('PostagemCategoria');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); 
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__.'_filter_nome')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_nome')); 
            }

            if (TSession::getValue(__CLASS__.'_filter_ativo')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ativo')); 
            }

            $criteria->add(new TFilter('deleted_at', 'IS', NULL));
            
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
            
            // reset the criteria for record count
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
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param);
        
        new TQuestion('<h5>Deseja realmente excluir esta categoria.</h5>', $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key=$param['key']; 
            TTransaction::open('app'); 
            $object = new Categoria($key, FALSE);

            $object->deleted_at = date('Y-m-d H:i:s');
            $object->store();
            
            TTransaction::close(); 
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', '<h5>Categoria excluída com sucesso!</h5>', $pos_action); 
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
