<?php

/**
 * AtendimentoList Listing
 * @author  Thayna Bezerra
 */
class AtendimentoList extends TPage
{
    private $form; 
    private $datagrid; 
    private $pageNavigation;
    private $loaded;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle('LISTAGEM DE ATENDIMENTOS');
        $this->form->setFieldSizes('100%');

        $paciente_id = new TDBCombo('paciente_id', 'app', 'Paciente', 'id', 'nome');
        
        $atendimento_data = new TDate('data_atendimento'); 
        $atendimento_data->setMask('dd/mm/yyyy');
        $atendimento_data->setDatabaseMask('yyyy-mm-dd');

        $this->form->addFields(
            [new TLabel('Paciente'), $paciente_id], 
            [new TLabel('Data'), $atendimento_data]
        )->layout = ['col-sm-8', 'col-sm-4']; 

        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Novo Atendimento', new TAction(['AtendimentoForm', 'onEdit']), 'fa:plus');
        $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser');

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $column_paciente = new TDataGridColumn('paciente->nome', 'Paciente', 'left');
        $column_data = new TDataGridColumn('data_atendimento', 'Data', 'center'); 
        $column_total = new TDataGridColumn('total', 'Total', 'center');

        $this->datagrid->addColumn($column_paciente);
        $this->datagrid->addColumn($column_data); 
        $this->datagrid->addColumn($column_total);

        $action_edit = new TDataGridAction(['AtendimentoForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-sm btn-primary');
        $action_edit->setLabel('Editar');
        $action_edit->setImage('far:edit');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($container);
    }

    public function onSearch($param)
    {
        $data = $this->form->getData();

        TSession::setValue(__CLASS__ . '_filter_paciente_id', NULL);
        TSession::setValue(__CLASS__ . '_filter_data_atendimento', NULL);

        if (isset($data->paciente_id) AND ($data->paciente_id)) {
            $filter = new TFilter('paciente_id', '=', $data->paciente_id);
            TSession::setValue(__CLASS__ . '_filter_paciente_id', $filter);
        }

        if (isset($data->data_atendimento) AND ($data->data_atendimento)) {
            $filter = new TFilter('data_atendimento', '=', $data->data_atendimento);
            TSession::setValue(__CLASS__ . '_filter_data_atendimento', $filter);
        }

        $this->form->setData($data);

        TSession::setValue(__CLASS__ . '_filter_data', $data);

        $param = [];
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    public function onClear($param)
    {
        TSession::setValue(__CLASS__ . '_filter_paciente_id', NULL);
        TSession::setValue(__CLASS__ . '_filter_data_atendimento', NULL);
        TSession::setValue(__CLASS__ . '_filter_data', NULL);

        $this->form->clear(TRUE);

        $this->onReload($param);
    }
    
    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('app');

            $repository = new TRepository('Atendimento');
            $limit = 10;

            $criteria = new TCriteria;
            if (empty($param['order'])) {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__ . '_filter_paciente_id')) {
                $criteria->add(TSession::getValue(__CLASS__ . '_filter_paciente_id'));
            }

            if (TSession::getValue(__CLASS__ . '_filter_data_atendimento')) {
                $criteria->add(TSession::getValue(__CLASS__ . '_filter_data_atendimento'));
            }

            // Restaura os valores do filtro no formulário
            $data = TSession::getValue(__CLASS__ . '_filter_data');
            if ($data) {
                $this->form->setData($data);
            }
                
            $pacientes = $repository->load($criteria);
            $this->datagrid->clear();
            
            if ($pacientes) {
                foreach ($pacientes as $paciente) {
                    $paciente->nome_paciente = $paciente->get_paciente()->nome;
                    $this->datagrid->addItem($paciente);
                }
            }
        
            $criteria->resetProperties();
            $count = $repository->count($criteria);

            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($limit);

            TTransaction::close();
            $this->loaded = true;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Shows the page
     */
    public function show()
    {
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  ['onReload', 'onSearch'])))) {
            if (func_num_args() > 0) {
                $this->onReload(func_get_arg(0));
            } else {
                $this->onReload();
            }
        }
        parent::show();
    }
}
?>
