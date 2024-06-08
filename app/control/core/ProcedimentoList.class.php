<?php

/**
 * ProcedimentoList Listing
 * @author  Thayna Bezerra
 */
class ProcedimentoList extends TPage
{
    private $form; 
    private $datagrid;
    private $pageNavigation;
    private $loaded;
    private $deleteButton;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_search_Procedimento');
        $this->form->setFormTitle('Listagem de Procedimentos');
        $this->form->setFieldSizes('100%');

        $titulo = new TEntry('titulo');
        $titulo->placeholder = "Digite o título do procedimento";

        $categoria_id = new TDBCombo('categoria_id', 'app', 'Categoria', 'id', 'titulo', 'titulo');
        $categoria_id->setDefaultOption('Selecione');

        $row = $this->form->addFields(
            [ new TLabel('Título'), $titulo ],
            [ new TLabel('Categoria'), $categoria_id ]
        );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        $btn = $this->form->addActionLink(('Novo Procedimento'), new TAction(
            ['ProcedimentoForm', 'onEdit']), 'fa:plus'
        );
        $btn->class = 'btn btn-sm btn-primary';

        $btn = $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default right';

        $btn = $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-default right';

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';

        $column_id = new TDataGridColumn('id', 'ID', 'center');
        $column_titulo = new TDataGridColumn('titulo', 'Título', 'left');
        $column_descricao = new TDataGridColumn('descricao', 'Descrição', 'left');
        $column_tempo = new TDataGridColumn('tempo', 'Tempo', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'center');

        $this->datagrid->addColumn($column_id)->setVisibility(false);
        $this->datagrid->addColumn($column_titulo);
        $this->datagrid->addColumn($column_descricao);
        $this->datagrid->addColumn($column_tempo);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_ativo);

        $column_tempo->setTransformer(function ($value, $object, $row) {
            if ($value) {
                //$datetime = DateTime::createFromFormat('H:i:s', $value)
                //Mascara para formatar o campo tempo na listagem
            }
            return $value;
        });
        

        $column_valor->setTransformer(function ($value, $object, $row) {
            return number_format($value, 2, ',', '.'); 
        });

        $column_ativo->setTransformer(function ($value, $object, $row) {
            return $value == 'S' ? 'Sim' : 'Não';
        });

        $action_edit = new TDataGridAction(['ProcedimentoForm', 'onEdit']);
        $action_edit->setButtonClass('btn btn-sm btn-primary');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit #020c44');
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

    public function onClear($param)
    {
        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_titulo', NULL);
        TSession::setValue(__CLASS__.'_filter_categoria_id', NULL);

        $this->form->clear(TRUE);
        $this->onReload($param);
    }

    public function onSearch()
    {
        $data = $this->form->getData();

        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filter_titulo', NULL);
        TSession::setValue(__CLASS__.'_filter_categoria_id', NULL);

        if (isset($data->titulo) AND ($data->titulo)) {
            $filter = new TFilter('titulo', 'like', "%{$data->titulo}%");
            TSession::setValue(__CLASS__.'_filter_titulo', $filter);
        }

        if (isset($data->categoria_id) AND ($data->categoria_id)) {
            $filter = new TFilter('categoria_id', '=', $data->categoria_id);
            TSession::setValue(__CLASS__.'_filter_categoria_id', $filter);
        }

        $this->form->setData($data);
        TSession::setValue(__CLASS__ . '_filter_data', $data);

        $param = [];
        $param['offset'] = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('app');
            $repository = new TRepository('Procedimento');
            $limit = 15;

            $criteria = new TCriteria;
            if (empty($param['order'])) {
                $param['order'] = 'titulo';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__.'_filter_titulo')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_titulo'));
            }

            if (TSession::getValue(__CLASS__.'_filter_categoria_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_categoria_id'));
            }

            $objects = $repository->load($criteria, FALSE);
            $this->datagrid->clear();
            if ($objects) {
                foreach ($objects as $object) {
                    $this->datagrid->addItem($object);
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

    public function show()
    {
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'], ['onReload', 'onSearch'])))) {
            if (func_num_args() > 0) {
                $this->onReload(func_get_arg(0));
            } else {
                $this->onReload();
            }
        }
        parent::show();
    }
}
