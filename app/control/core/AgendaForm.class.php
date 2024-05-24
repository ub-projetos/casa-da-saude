<?php

/**
 * PacienteForm Form
 * @author  Luan kloh
 */
class AgendaForm extends TPage
{
    protected $form; // form
    
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();      
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Agenda');
        $this->form->setFormTitle('FORMULÁRIO DE AGENDA');
        $this->form->setFieldSizes('100%');
        
        // create the form fields
        $id = new THidden('id');

        $profissional_id = new TDBUniqueSearch('profissional_id', 'app', 'ViewProfissional', 'id', 'name', 'name');
        $profissional_id->setMask('{name}');
        $profissional_id->addValidation('Profissional', new TRequiredValidator());
        $profissional_id->setMinLength(1);

        $data_inicial = new TDate('data_inicial');
        $data_inicial->setMask("dd/mm/yyyy");
        $data_inicial->setDatabaseMask("yyyy-mm-dd");
        $data_inicial->placeholder = "dd/mm/aaaa";
        $data_inicial->addValidation('data inicial', new TRequiredValidator);

        $data_final = new TDate('data_final');
        $data_final->setMask("dd/mm/yyyy");
        $data_final->setDatabaseMask("yyyy-mm-dd");
        $data_final->placeholder = "dd/mm/aaaa";
        $data_final->addValidation('data final', new TRequiredValidator);
        

        $ativa = new TRadioGroup('ativa');
        $ativa->addItems([
            "Y" => 'Sim',
            "N" => 'Não'
        ]);
        $ativa->setLayout('horizontal');
        $ativa->setUseButton();
        $ativa->setSize(112);
        $ativa->setValue("Y");

        #foreach ($ativa->getLabels() as $key => $label)
        #{
        #   # $label->setTip("Check $key");
        #    $label->setSize(110);
        #}

        $horario = new TCheckGroup('horario');
        $horario->addItems(AgendaItem::list_horarios());
        $horario->setLayout('horizontal');
        $horario->setBreakItems(10);
        $horario->setUseButton();
        $horario->addValidation('horario', new TRequiredValidator);
        #$horario->addValidation('Field 1', new TMinLengthValidator, array(3))


        foreach ($horario->getLabels() as $key => $label)
        {
           # $label->setTip("Check $key");
            $label->setSize(100);
        }
        
        #Pessoa Fisica
        



        // add the fields
        $this->form->addFields( [ $id ] );

        #Pessoa Fisica

        $row = $this->form->addFields(
            [ new TLabel('Proficional'), $profissional_id ],
        );
        $row->layout = ['col-sm-8'];

        $row = $this->form->addFields(
            [ new TLabel('Data inicial'), $data_inicial ],
            [ new TLabel('Data final'), $data_final ],
            [ new TLabel('Ativa'), $ativa ],
        );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-2'];

        $row = $this->form->addFields(  
            [ new TLabel('Horário'), $horario],
        );
        $row->layout = ['col-sm-12'];
        
        
        

        // create the form actions

        $btn = $this->form->addAction('SALVAR', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary left';
        
        $btn = $this->form->addActionLink('Volvar', new TAction(['AgendaList', 'onReload']), 'fa:times red');
        $btn->class = 'btn btn-sm btn-default right';

        $btn = $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser');
        $btn->class = 'btn btn-sm btn-default right';

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('app'); 
            
            $this->form->validate(); 
            $data = $this->form->getData(); 
            if (!empty($data->id)) {
                $data->data_final = $data->data_inicial;
            } else {
                $repository = new TRepository('Agenda');
                $criteria = new TCriteria;
                $criteria->add(new TFilter('date', 'between', $data->data_inicial, $data->data_final));
                $criteria->add(new TFilter('profissional_id', '=', $data->profissional_id));
                $objects = $repository->load($criteria, FALSE);

                if ($objects) {throw new Exception('<h5>Já existe agenda cadastrada nesse periodo para esse profissional</h5>');}
            }

            
            
            
            if (new DateTime($data->data_inicial) > new DateTime($data->data_final)) {
                throw new Exception('<h5>A data inicial não pode ser maior que a data final!</h5>');
            }

            $data_dif = date_diff(new DateTime($data->data_inicial), new DateTime($data->data_final))->days;
            

            for ($i=0; $i <= $data_dif; $i++) {
                $object = new Agenda();
                
                $object->id = $data->id;
                $object->system_user_id = SystemUser::id();
                $object->profissional_id = $data->profissional_id;
                $object->date = date("Y-m-d", strtotime("+{$i} days",strtotime($data->data_inicial)));
                $object->ativa = $data->ativa;
                
                $object->store();
                $object->clearParts();
                #var_dump($data->horario);

                foreach ($data->horario as  $key=>$tempo) {
                    $agenda_item = new AgendaItem();

                    $agenda_item->agenda_id = $object->id;
                    $agenda_item->hora = $tempo;
                    $agenda_item->store();
                }
            }

            TTransaction::close(); 
            
            new TMessage('info', '<h5>Agenda salvo com sucesso!</h5>', new TAction(
                ['AgendaList', 'onReload']
            ));
        
        }catch (Exception $e) {
            new TMessage('error', $e->getMessage()); 

            $data = $this->form->getData();
            if (!empty($data->id)) {
                TDate::disableField('form_Agenda', 'data_final');
            }
            

            $this->form->setData($data);
            TTransaction::rollback();
        }
    }
    
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key']; 

                TTransaction::open('app'); 
                $object = new Agenda($key);
                $object->data_inicial = $object->date;
                $object->data_final = $object->date;

                $teste = [];
                $itens = $object->getAgendaItem();

                foreach ($itens as $key => $value) {
                    $teste[] = $value->hora;
                }
                $object->horario = $teste;

                $this->form->setData($object);
                TDate::disableField('form_Agenda', 'data_final');
                TTransaction::close();

            }else{
                $this->form->clear(TRUE);
            }

        } catch (Exception $e){
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }  
}