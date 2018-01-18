<?php

namespace Treto\Import1CBundle\Command\NoExecute;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Treto\Import1CBundle\Helper\DateTimeHelper;

/**
 * Class TestK50Command
 * php bin/console test:k50 --chipCount=18 --fieldsCount=36 --env=te
 * @package Treto\Import1CBundle\Command\NoExecute
 */
class TestK50Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('test:k50')
            ->setDescription('казино к50')
            ->addOption(
                'fieldsCount',
                'fc',
                InputOption::VALUE_REQUIRED,
                'количество ячеек ',
                0
            )
            ->addOption(
                'chipCount',
                'cc',
                InputOption::VALUE_REQUIRED,
                'количество фишек',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '8000M');
        $ymTime = microtime(true);
        $output->writeln("Start ..." . DateTimeHelper::getDateString());

        $fieldsCount = $input->getOption('fieldsCount');
        $chipCount = $input->getOption('chipCount');
        if ($fieldsCount == 0 or $chipCount == 0) {
            $output->writeln("не введены параметры");

            return;
        }
        if ($fieldsCount <= $chipCount) {
            $output->writeln("фишек больше ячеек");

            return;
        }
        $maxFlout = pow(2, $fieldsCount) - 1;
        $startFlout = pow(2, $chipCount);
        $out = [];
        $count = 0;
        for ($i = $maxFlout; $i >= $startFlout; $i--) {
            $echo = decbin($i - 1);
            $digit = str_replace('0', '', $echo);
            if (strlen($digit) == $chipCount) {
                $key = md5($echo);
                $echo = str_pad($echo, $fieldsCount, "0", STR_PAD_LEFT);
                $out[$key] = $echo;
                $output->write(sprintf("\r* Сохранено в массив: %4d of %d", count($out), $maxFlout));
            }
            $count++;
        }
        $echo = PHP_EOL . "Подготвлены данные в количестве " . count($out);
        $output->writeln($echo);
        $this->printData($out);
        $ymTime = microtime(true) - $ymTime;
        $echo = "Затрачено время " . $ymTime;
        $output->writeln($echo);
        $output->writeln("End " . DateTimeHelper::getDateString());
    }

    /**
     * печать полученных данных
     * @param $out
     */
    protected function printData($out)
    {

        $file = 'out-' . DateTimeHelper::getDateString() . '.txt';
        $len = count($out);
        if ($len < 10) {
            $str = 'менее 10 вариантов';

        } else {
            $str = $len . ' вариантов' . PHP_EOL;
            foreach ($out as $t) {
                $str .= $t . PHP_EOL;
            }
        }

        file_put_contents($file, $str, FILE_APPEND);
    }
//    static public function getDateString($date = null, $format = null)
//    {
//        $format = $format ? $format : 'Y-m-d H:i:s';
//        $date = $date ? $date : new DateTime();
//
//        return $date->format($format);
//    }
}
