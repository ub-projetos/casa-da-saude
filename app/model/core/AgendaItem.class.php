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
    private $atendimentoitem;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('agenda_id'); 
        parent::addAttribute('atendimento_item_id'); 
        parent::addAttribute('hora');
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

    public function set_item(AtendimentoItem $object)
    {
        $this->atendimentoitem = $object;
        $this->atendimento_item_id = $object->id;
    }

    public function get_item()
    {
        if (empty($this->atendimentoitem))
            $this->atendimentoitem = new AtendimentoItem($this->atendimento_item_id);

        return $this->atendimentoitem;
    }
}
