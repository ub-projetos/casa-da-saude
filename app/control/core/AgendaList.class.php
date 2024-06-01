<?php
/**
 * PacienteList Listing
 * @author  Luan kloh
 */
class AgendaList extends TPage
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
        $this->form->setFormTitle('LISTAGEM DE AGENDAS');
        $this->form->setFieldSizes('100%');

        // Criar os campos do formulário
        //$id = new TEntry('id');
        $data_agenda = new TEntry('data_agenda');
        $data_agenda->setMask("dd/mm/yyyy");
        $data_agenda->placeholder = "dd/mm/aaaa";

        $profissional_id = new TDBUniqueSearch('profissional_id', 'app', 'ViewProfissional', 'id', 'name', 'name');
        $profissional_id->setMask('{name}');
        $profissional_id->setMinLength(1);

        $ativa = new TCombo('ativa');
        $ativa->setDefaultOption("Selecione");
        $ativa->addItems([
            "Y" => 'sim',
            "N" => 'não'
        ]);
        

        // Adicionar os campos ao formulário
        
        $row = $this->form->addFields(
            [ new TLabel('Data'), $data_agenda ],
            [ new TLabel('Profissional'), $profissional_id ],
            [ new TLabel('Ativo'), $ativa ],
        );
        $row->layout = ['col-sm-3', 'col-sm-6', 'col-sm-3'];

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addActionLink(('Nova Agenda'), new TAction(
            ['AgendaForm', 'onEdit']), 'fa:plus'
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
        $column_system_user_id = new TDataGridColumn('system_user_id', 'ID_USER', 'center');
        $column_data_agenda = new TDataGridColumn('data_agenda', 'DATA', 'center');
        $column_profissional_id = new TDataGridColumn('profissional_id', 'NOME&nbsp;PROFISSIONAL', 'center');
        $column_ativa = new TDataGridColumn('ativa', 'ATIVO', 'center');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id)->setVisibility(false);
        $this->datagrid->addColumn($column_system_user_id)->setVisibility(false);
        $this->datagrid->addColumn($column_data_agenda);
        $this->datagrid->addColumn($column_profissional_id);
        $this->datagrid->addColumn($column_ativa);

        $column_profissional_id->setTransformer(function ($value, $object, $row) {
            return SystemUser::where('id', '=', $value)->first()->name;
        });
        
        $column_ativa->setTransformer( function($value, $object, $row) {
            $class = ($value=='Y') ? 'success' : 'danger';
            $label = ($value=='Y') ? _t('Yes') : _t('No');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);
            return $div;
        });


        $column_data_agenda->setTransformer( function($value, $object, $row) {
            if(!empty($value)){
                $date = new DateTime($value);
                return $date->format('d/m/Y');
            }
        });

        #$column_celular->setTransformer( function($value, $object, $row) {
        #    return AppHelper::formatPhone($value);
        #});

        #$column_cpf->setTransformer( function($value, $object, $row) {
        #    return AppHelper::formatCpfCnpj($value);
        #});
        
        
        $action_edit = new TDataGridAction(['AgendaForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-sm btn-primary');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit #020c44');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        $action_delete = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action_delete->setLabel(_t('Delete'));
        $action_delete->setImage('far:trash-alt red');
        $this->datagrid->addAction($action_delete);
        
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
        TSession::setValue(__CLASS__.'_filter_profissional_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_agenda',   NULL);
        TSession::setValue(__CLASS__.'_filter_ativa',   NULL);

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
        TSession::setValue(__CLASS__.'_filter_profissional_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_agenda',   NULL);
        TSession::setValue(__CLASS__.'_filter_ativa',   NULL);


        if (isset($data->profissional_id) AND ($data->profissional_id)) {
            $filter = new TFilter('profissional_id', 'like', "%{$data->profissional_id}%"); 
            TSession::setValue(__CLASS__.'_filter_profissional_id',   $filter);
        }

        if (isset($data->data_agenda) and ($data->data_agenda)) {
            $filter = new TFilter('data_agenda', '=', $data->data_agenda); 
            TSession::setValue(__CLASS__.'_filter_data_agenda', $filter);
        }

        if (isset($data->ativa) AND ($data->ativa)) {
            $filter = new TFilter('ativa', '=', $data->ativa); 
            TSession::setValue(__CLASS__.'_filter_ativa',   $filter); 
        }        
        
        $this->form->setData($data);
        
        
        TSession::setValue(__CLASS__.'_filter_data', $data);
        
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
            
            $repository = new TRepository('Agenda');
            $limit = 15;
            
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
                $criteria->add($filter = new TFilter('data_agenda', '>=', date('Y-m-d'))); 
            }

            if (TSession::getValue(__CLASS__.'_filter_ativa')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ativa')); 
            } else {
                $criteria->add(new TFilter('ativa', '=', "Y")); 
            }

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
        
        new TQuestion("<h5>Deseja excluir essa Agenda?</h5>", $action);
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
            $object = new Agenda($key, FALSE);
            $object->deleted_at = date('Y-m-d H:i:s');
            $object->store();
            TTransaction::close();
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', "<h5>Registro excluído com sucesso!</h5>", $pos_action); // success message
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
