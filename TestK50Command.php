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
    private $file;
    public function __construct()
    {
        $this->file = 'out-' . DateTimeHelper::getDateString() . '.txt';

        parent::__construct();
    }

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
        $count = 0;
        $outputCount = $this->checkOutputCount($fieldsCount, $chipCount );
        $t = 1;
        if($outputCount>10){
            $str = $this->printThenCount($outputCount);
            $output->writeln($str);
            $out = [];
            for ($i = $maxFlout; $i >= $startFlout; $i--) {
                $echo = decbin($i - 1);
                $digit = str_replace('0', '', $echo);
                if (strlen($digit) == $chipCount) {
                    $key = md5($echo);
                    $echo = str_pad($echo, $fieldsCount, "0", STR_PAD_LEFT);
                    $out[$key] = $echo;
                    $output->write(sprintf("\r* Сохранено в массив: %4d of %d", count($out), $maxFlout));
                    $t++;
                    if($t>100){
                        $this->printData($out);
                        $t=0;
                        $out = [];
                    }
                }
                $count++;
            }
            if($t>0){
                $this->printData($out);
            }

        }else{
            $str = $this->printLessCount();
            $output->writeln($str);

            return;
        }
        $echo = PHP_EOL . "Подготвлены данные в количестве " . count($out);
        $output->writeln($echo);
//        $this->printData($out);
        $ymTime = microtime(true) - $ymTime;
        $echo = "Затрачено время " . $ymTime;
        $output->writeln($echo);
        $output->writeln("End " . DateTimeHelper::getDateString());
    }

    private function printLessCount()
    {
        $str = 'менее 10 вариантов';
        file_put_contents($this->file, $str, FILE_APPEND);

        return $str;
    }
    private function printThenCount($outputCount)
    {
        $str = $outputCount . ' вариантов' . PHP_EOL;
        file_put_contents($this->file, $str, FILE_APPEND);

        return $str;
    }

    function checkOutputCount($fieldsCount, $chipCount)
    {
        $s1 = $this->factorial($fieldsCount);
        $s2 = $this->factorial($chipCount);
        $_s2 = $this->factorial($fieldsCount- $chipCount);
        $_s1 = $_s2 *$s2;
        $outputCount = $s1 / $_s1 ;

        return $outputCount;
    }

    /**
     * @param $x
     * @return int
     */
    function factorial($x)
    {
        if ($x === 0) return 1;
        else return $x*$this->factorial($x-1);
    }
    /**
     * печать полученных данных
     * @param $out
     */
    protected function printData($out)
    {
        $str = '';
        foreach ($out as $t) {
            $str .= $t . PHP_EOL;
        }
        file_put_contents($this->file, $str, FILE_APPEND);
    }
}
