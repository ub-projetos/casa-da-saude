<?php
/**
 * ViewProfissional
 *
 * @version    7.6
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class ViewProfissional extends TRecord
{
    const TABLENAME = 'view_profissional';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('login');
        parent::addAttribute('password');
        parent::addAttribute('email');
        parent::addAttribute('phone');
        parent::addAttribute('address');
        parent::addAttribute('function_name');
        parent::addAttribute('about');
        parent::addAttribute('frontpage_id');
        parent::addAttribute('system_unit_id');
        parent::addAttribute('active');
        parent::addAttribute('accepted_term_policy');
        parent::addAttribute('accepted_term_policy_at');
        parent::addAttribute('accepted_term_policy_data');
        parent::addAttribute('custom_code');
        parent::addAttribute('otp_secret');
    }
}