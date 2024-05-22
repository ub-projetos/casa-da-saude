<?php

/**
 * PacienteForm Form
 * @author  <your name here>
 */
class PacienteForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Paciente');
        $this->form->setFormTitle('FORMULÁRIO DE PACIENTE');
        $this->form->setFieldSizes('100%');
        
        // create the form fields
        $id = new THidden('id');

        /*$active = new TRadioGroup('active');
        $active->addItems(['Y'=>_t('Yes'),'N'=> _t('No')]);
        $active->setLayout('horizontal');
        $active->setUseButton();
        $active->setValue('Y');*/

        #Pessoa Fisica

        $nome = new TEntry('nome');
        $nome->addValidation('Nome', new TRequiredValidator());
        $nome->forceUpperCase();

        $cpf = new TEntry('cpf');
        $cpf->addValidation('CPF', new TCPFValidator());
        $cpf->setMask('999.999.999-99', true);

        $nascimento = new TDate('nascimento');
        $nascimento->setMask("dd/mm/yyyy");
        $nascimento->setDatabaseMask("yyyy-mm-dd");
        $nascimento->placeholder = "dd/mm/aaaa";

        $celular = new TEntry('celular');
        $celular->addValidation('Celular', new TRequiredValidator());
        $celular->setMask('(99)99999-9999', true);

        $email = new TEntry('email');
        $email->addValidation('E-mail', new TRequiredValidator());





        /*ENDEREÇO*/

        /*$cep = new TEntry('cep');
        $cep->setMask("99.999-999", true);

        $address = new TEntry('address');

        $number = new TEntry('number');

        $complement = new TEntry('complement');       

        $district = new TEntry('district');

        $city = new TEntry('city');

        $uf = new TCombo('uf');
        $uf->setDefaultOption('Selecione');
        $uf->addItems(Person::list_uf());

        $referencia = new TEntry('reference');*/
        
        $obs = new TText('obs');
        $obs->placeholder = ' Digite aqui...';

        /* CONTATO */





        // add the fields
        $this->form->addFields( [ $id ] );

        #Pessoa Fisica

        $row = $this->form->addFields(
            [ new TLabel('CPF *'), $cpf ],
            [ new TLabel('Nome *'), $nome ], 
            
        );
        $row->layout = ['col-sm-4', 'col-sm-8'];

        $row = $this->form->addFields(
            [ new TLabel('Nascimento'), $nascimento ],
            [ new TLabel('Celular *'), $celular ],
            [ new TLabel('E-mail *'), $email ],
        );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];



        $this->form->addFields([new TFormSeparator('Observações')]); 

        $row = $this->form->addFields(
            [$obs ], 
        );
        $row->layout = ['col-sm-12'];

        // create the form actions

        $btn = $this->form->addAction('SALVAR', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary left';
        
        $btn = $this->form->addActionLink('Volvar', new TAction(['PacienteList', 'onReload']), 'fa:times red');
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
            
            $object = new Paciente;
            $object->fromArray( (array) $data);
            //$object->system_user_id = SystemUser::id();

            $object->store(); 

            TTransaction::close(); 
            
            new TMessage('info', '<h5>Paciente salvo com sucesso!</h5>', new TAction(
                ['PacienteList', 'onReload']
            ));
        
        }catch (Exception $e) {
            new TMessage('error', $e->getMessage()); 

            $data = $this->form->getData();

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
                $object = new Paciente($key); 


                $this->form->setData($object);

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