<?php

/**
 * ProcedimentoForm Form
 * @author  Thayna Bezerra
 */
class ProcedimentoForm extends TPage
{
    protected $form;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_Procedimento');
        $this->form->setFormTitle('FORMULÁRIO DE PROCEDIMENTO');
        $this->form->setFieldSizes('100%');

        $id = new THidden('id');
        $categoria_id = new TDBCombo('categoria_id', 'app', 'Categoria', 'id', 'titulo', 'titulo');
        $categoria_id->setDefaultOption('Selecione');

        $titulo = new TEntry('titulo');
        $titulo->addValidation('Título', new TRequiredValidator());

        $descricao = new TText('descricao');
        
        $tempo = new TEntry('tempo');
        $tempo->addValidation('Tempo', new TRequiredValidator());
        $tempo->setMask('99:99:99', true); 

        $valor = new TEntry('valor');
        $valor->addValidation('Valor', new TRequiredValidator());
        $valor->setNumericMask(2, ',', '.', false); 
        $valor->style = 'text-align: left;'; 

        $ativo = new TRadioGroup('ativo');
        $ativo->addItems(['S' => 'Sim', 'N' => 'Não']);
        $ativo->setLayout('horizontal');
        $ativo->setUseButton();
        $ativo->setValue('S');

        $this->form->addFields([new TLabel('Categoria')], [$categoria_id]);
        $this->form->addFields([new TLabel('Título')], [$titulo]);
        $this->form->addFields([new TLabel('Descrição')], [$descricao]);
        $this->form->addFields([new TLabel('Tempo')], [$tempo]);
        $this->form->addFields([new TLabel('Valor')], [$valor]);
        $this->form->addFields([new TLabel('Ativo')], [$ativo]);
        $this->form->addFields([$id]);

        $btn = $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary left';

        $btn = $this->form->addActionLink('Voltar', new TAction(['ProcedimentoList', 'onReload']), 'fa:times red');
        $btn->class = 'btn btn-sm btn-default right';

        $btn = $this->form->addActionLink('Limpar', new TAction([$this, 'onEdit']), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default right';

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
            $data = $this->form->getData();

            $data->valor = str_replace(['.', ','], ['', '.'], $data->valor);

            $object = new Procedimento;
            $object->fromArray((array)$data);
            $object->store();

            TTransaction::close();

            new TMessage('info', 'Procedimento salvo com sucesso!', new TAction(['ProcedimentoList', 'onReload']));
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $this->form->setData($this->form->getData());
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
                $object = new Procedimento($key);
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
