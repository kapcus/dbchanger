<?php

namespace Kapcus\DbChanger\Command;

use Kapcus\DbChanger\Model\Reporting\Row;
use Kapcus\DbChanger\Model\Reporting\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class FormattedOutputCommand extends Command
{

	public function __construct()
	{

		parent::__construct();
	}

	protected function displayTable(OutputInterface $output, Table $table)
	{
		$this->writeTableSeparator($output, $table->getWidth());
		foreach ($table->getRows() as $row) {
			$output->writeln($this->formatTableRow($row));
			if ($row->isHeader()) {
				$this->writeTableSeparator($output, $table->getWidth());
			}
		}
	}

	protected function writeTableSeparator(OutputInterface $output, $width) {
		$output->writeln(str_pad('',  $width, "-"));
	}

	protected function formatTableRow(Row $row)
	{
		$chunks = [];
		foreach($row->getCells() as $cell) {
			$tags = sprintf($row->isHeader() ? '<info>' : '');
			$chunks[] = $tags.sprintf('%'.$cell->getColumn()->getWidth().'s', $row->isHeader() ? $cell->getColumn()->getTitle() : $cell->getValue()).$tags;
		}
		return implode(' | ', $chunks);
	}
}