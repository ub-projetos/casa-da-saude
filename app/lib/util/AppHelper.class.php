<?php
/**
 * AppHelper
 *
 * @package    model
 * @subpackage util
 * @author     Thayna Bezerra
 */
class AppHelper
{
    public static function lista_ativo()
    {
        return [
            'Y' => 'Sim',
            'N' => 'Não'
        ];
    }


    public static function formatPhone($value)
    {
        if (strlen(strval($value)) == 11) {
            $value = preg_replace("/(\d{2})(\d{1})(\d{4})(\d{4})/", "(\$1) \$2 \$3-\$4", $value);
        } else {
            if (strlen(strval($value)) == 10) {
                $value = preg_replace("/(\d{2})(\d{4})(\d{4})/", "(\$1) \$2-\$3", $value);
            }
        }
        return $value;
    }


    public static function formatCEP($value)
    {
        if (strlen(strval($value)) == 8) {
            $value = preg_replace('/([0-9]{5})([0-9]{3})/', '$1-$2', $value);
        }
        return $value;
    }

    public static function formatCpfCnpj($value)
    {
        if (strlen($value) == 14) {
            $value = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $value);
        } else {
            if (strlen($value) == 11) {
                $value = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $value);
            }
        }
        return $value;
    }

    public static function toDateBR($value)
    {
        if($value){
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        }
        return $value;
    }

    public static function toDateTimeBR($value)
    {
        $date = new DateTime($value);
        return $date->format('d/m/Y H:i:s');
    }

    public static function toDouble($value)
    {
        setlocale(LC_ALL, 'en_US.UTF-8');
        if (is_array($value)) {
            $values = array();
            foreach ($value as $val) {
                $values[] = self::toDouble($val);
            }
            return $values;
        } else {
            if (is_int(strpos((string)$value, ','))) {
                $value = str_replace('R$', '', (string)$value);
                $value = str_replace('%', '', (string)$value);
                $value = str_replace('.', '', (string)$value);
                $value = str_replace(',', '.', (string)$value);
            }
            return (double) $value;
        }
    }

    public static function toNumeric($value, $casas = 2)
    {
        if($value){
            return number_format((string) $value, $casas, ',', '.');
        }
    }

    public static function toMonetary($value)
    {
        if($value){
            return 'R$&nbsp;' . number_format((string)$value, 2, ',', '.');
        }
    }

    public static function toOnlyNumber($value)
    {
        if($value){
            return preg_replace("/[^0-9]/", "", $value);
        }
    }


}
