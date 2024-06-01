<?php
/**
 * AgendaItem
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class AgendaItem extends TRecord
{
    const TABLENAME = 'agenda_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $agenda;
    private $atendimento_item;
    private $horario;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('agenda_id'); 
        parent::addAttribute('atendimento_item_id'); 
        parent::addAttribute('horario_id');
    }

    public function set_agenda(Agenda $object)
    {
        $this->agenda = $object;
        $this->agenda_id = $object->id;
    }

    public function get_agenda()
    {
        if (empty($this->agenda))
            $this->agenda = new Agenda($this->agenda_id);

        return $this->agenda;
    }

    public function set_atendimento_item(AtendimentoItem $object)
    {
        $this->atendimento_item = $object;
        $this->atendimento_item_id = $object->id;
    }

    public function get_atendimento_item()
    {
        if (empty($this->atendimento_item))
            $this->atendimento_item = new AtendimentoItem($this->atendimento_item_id);

        return $this->atendimento_item;
    }

    public function set_horario(Horario $object)
    {
        $this->horario = $object;
        $this->horario_id = $object->id;
    }

    public function get_horario()
    {
        if (empty($this->horario))
            $this->horario = new Horario($this->horario_id);

        return $this->horario;
    }


}
