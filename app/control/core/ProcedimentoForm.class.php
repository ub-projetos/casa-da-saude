<?php
/**
 * ProcedimentoList
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class ProcedimentoList extends TRecord
{
    const TABLENAME = 'procedimento_List';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('procedimento_id'); 
        parent::addAttribute('profissional_id'); 
    





        // Creates the DataGrid
        $this->datagrid = new TDataGrid;

        // Define the columns
        $id = new TDataGridColumn('id', 'ID', 'center', 50);
        $usuario = new TDataGridColumn('system_user_id', 'Usuário', 'left');
        $profissional = new TDataGridColumn('profissional_id', 'Profissional', 'left');
        $data = new TDataGridColumn('data', 'Data', 'center');
        $ativa = new TDataGridColumn('ativa', 'Ativa', 'center');
        $created_at = new TDataGridColumn('created_at', 'Criado em', 'center');
        $updated_at = new TDataGridColumn('updated_at', 'Atualizado em', 'center');

        // Add the columns to the datagrid
        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($usuario);
        $this->datagrid->addColumn($profissional);
        $this->datagrid->addColumn($data);
        $this->datagrid->addColumn($ativa);
        $this->datagrid->addColumn($created_at);
        $this->datagrid->addColumn($updated_at);

        // Create datagrid actions
        $edit_action = new TDataGridAction(['AgendamentoForm', 'onEdit'], ['id' => '{id}']);
        $delete_action = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

        // Add actions to the datagrid
        $this->datagrid->addAction($edit_action, 'Editar', 'fa:edit blue');
        $this->datagrid->addAction($delete_action, 'Deletar', 'fa:trash red');

        // Create the datagrid model
        $this->datagrid->createModel();

        // Wrap the datagrid in a panel
        $panel = new TPanelGroup('Lista de Agendamentos');
        $panel->add(new BootstrapDatagridWrapper($this->datagrid));

        // Add the panel to the page
        parent::add($panel);
    }

    public function onReload($param = NULL)
    {
        try
        {
            TTransaction::open('database');
            $repository = new TRepository('Agendamento');
            $criteria = new TCriteria;
            $objects = $repository->load($criteria);

            $this->datagrid->clear();
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onDelete($param)
    {
        try
        {
            $id = $param['id'];
            TTransaction::open('casa-da-saude');
            $agendamento = new Agendamento($id);
            $agendamento->delete();
            TTransaction::close();
            $this->onReload();
            new TMessage('info', 'Agendamento deletado com sucesso');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function show()
    {
        $this->onReload();
        parent::show();
    }
}
?>
