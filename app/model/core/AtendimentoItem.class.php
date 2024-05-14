<?php
/**
 * AtendimentoItem
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class AtendimentoItem extends TRecord
{
    const TABLENAME = 'atendimento_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $atendimento;
    private $procedimento;
    private $profissional;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('atendimento_id'); 
        parent::addAttribute('procedimento_id'); 
        parent::addAttribute('profissional_id'); 
        parent::addAttribute('valor');
    }

    public function set_atendimento(Atendimento $object)
    {
        $this->atendimento = $object;
        $this->atendimento_id = $object->id;
    }

    public function get_atendimento()
    {
        if (empty($this->atendimento))
            $this->atendimento = new Atendimento($this->atendimento_id);

        return $this->atendimento;
    }

    public function set_procedimento(Procedimento $object)
    {
        $this->procedimento = $object;
        $this->procedimento_id = $object->id;
    }

    public function get_procedimento()
    {
        if (empty($this->procedimento))
            $this->procedimento = new Procedimento($this->procedimento_id);

        return $this->procedimento;
    }

    public function set_profissional(SystemUser $object)
    {
        $this->profissional = $object;
        $this->profissional_id = $object->id;
    }

    public function get_profissional()
    {
        if (empty($this->profissional))
            $this->profissional = new SystemUser($this->profissional_id);

        return $this->profissional;
    }
}
