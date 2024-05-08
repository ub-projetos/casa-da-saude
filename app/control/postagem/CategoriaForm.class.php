<?php
/**
 * CategoriaForm Form
 * @author  <your name here>
 */
class CategoriaForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Categoria');
        $this->form->setFormTitle('FORMULÁRIO DE CATEGORIA');
        $this->form->setFieldSizes('100%');
        

        // create the form fields
        $id = new THidden('id');
           
        $titulo = new TEntry('titulo');
        $titulo->addValidation('Título', new TRequiredValidator);

        $ativo = new TCombo('ativo');
        $ativo->setDefaultOption(false);
        $ativo->addItems(PostagemCategoria::list_items());


        // add the fields
        $this->form->addFields( [ $id ] );
        
        $row = $this->form->addFields(
            [ new TLabel('Título'), $titulo ],
            [ new TLabel('Ativo'), $ativo ],
        );
        $row->layout = ['col-sm-10', 'col-sm-2'];
         
        // create the form actions
        $btn = $this->form->addAction('SALVAR', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('CategoriaList','onReload')), 'far:arrow-alt-circle-left blue');

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
        try{

            TTransaction::open('app'); 
            
            $this->form->validate(); 
            $data = $this->form->getData(); 
            
            $object = new PostagemCategoria;
            $object->fromArray( (array) $data); 
            $object->store(); 
            
            TTransaction::close(); 
            
            new TMessage('info', '<h5>Categoria salva com sucesso!</h5>', new TAction(['CategoriaList', 'onReload']));
            
        }catch (Exception $e){
            new TMessage('error', $e->getMessage()); 
            $this->form->setData( $this->form->getData() ); 
            TTransaction::rollback(); 
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
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
                $object = new PostagemCategoria($key); 

                $this->form->setData($object); 
                TTransaction::close(); 
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage()); 
            TTransaction::rollback(); 
        }
    }
}
