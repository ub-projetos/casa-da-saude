<?php

/**
 * AtendimentoForm Form
 * @author  Thayna Bezerra
 */
class AtendimentoForm extends TPage
{
    protected $form; 

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_Atendimento');
        $this->form->setFormTitle('FORMULÁRIO DE ATENDIMENTO');
        $this->form->setFieldSizes('100%');

        $id = new THidden('id');

        $paciente_id = new TDBUniqueSearch('paciente_id', 'app', 'Paciente', 'id', 'nome', 'nome');
        $paciente_id->setMask('{nome} - {cpf}');
        $paciente_id->addValidation('Paciente', new TRequiredValidator());
        $paciente_id->setMinLength(1);

        $data_atendimento = new TDate('data_atendimento'); 
        $data_atendimento->setMask('dd/mm/yyyy');
        $data_atendimento->setDatabaseMask('yyyy-mm-dd');
        $data_atendimento->addValidation('Data', new TRequiredValidator); 

        $total = new TEntry('total');
        $total->setMask('999999.99'); 
        $total->addValidation('Total', new TRequiredValidator); 

        $this->form->addFields([$id]);
        $this->form->addFields([new TLabel('Paciente'), $paciente_id]);
        $this->form->addFields([new TLabel('Data'), $data_atendimento], [new TLabel('Total'), $total]);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save');
        $this->form->addActionLink('Voltar', new TAction(['AtendimentoList', 'onReload']), 'fa:times');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave($param)
    {
        try {
            TTransaction::open('app');

            $this->form->validate();
            $data_atendimento = $this->form->getData();

            $object = new Atendimento;
            $object->fromArray((array)$data_atendimento);
            $object->store();

            TTransaction::close();

            new TMessage('info', 'Atendimento salvo com sucesso!', new TAction(['AtendimentoList', 'onReload']));
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $this->form->setData($data_atendimento);
            TTransaction::rollback();
        }
    }

    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                $key = $param['key'];

                TTransaction::open('app');
                $object = new Atendimento($key);
                $this->form->setData($object);
                TTransaction::close();
            } else {
                $this->form->clear(TRUE);
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
?>
