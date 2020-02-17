<?php

namespace App;

use Symfony\Component\ErrorHandler\Error\FatalError;

class Aacms
{
    public static function render($__php, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        try {
            eval('?' . '>' . $__php);
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            throw $e;
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) ob_end_clean();
            throw new FatalError('Error', 0, $e);
        }
        return ob_get_clean();
    }
}
