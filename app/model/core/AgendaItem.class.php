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

    public static function list_horarios()
    {
        return [
            '1' => "08:00",
            '2' => "08:30",
            '3' => "09:00",
            '4' => "09:30",
            '5' => "10:00",
            '6' => "10:30",
            '7' => "11:00",
            '8' => "11:30",
            '9' => "12:00",
            '10' => "12:30",
            '11' => "13:00",
            '12' => "13:30",
            '13' => "14:00",
            '14' => "14:30",
            '15' => "15:00",
            '16' => "15:30",
            '17' => "16:00",
            '18' => "16:30",
            '19' => "17:00",
            '20' => "17:30"
        ];
    }

}
