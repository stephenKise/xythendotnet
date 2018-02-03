<?php
declare(strict_types=1);

namespace LOTGD;

class DiceBag
{
    public $rollData = [
        'amount' => 0,
        'sides' => 0,
        'modifier' => 0,
        'total' => 0,
        'dice' => []
    ];

    public function __construct(int $amount, int $sides, string $modifier)
    {
        $this->rollData['amount'] = $amount;
        $this->rollData['sides'] = $sides;
        $this->rollData['modifier'] = $modifier;
    }

    public function roll(): array
    {
        for ($i = 0; $i < $this->rollData['amount']; $i++) {
            $random = rand(1, $this->rollData['sides']);
            $random += intval($this->rollData['modifier']);
            $this->rollData['total'] += $random;
            $this->rollData['dice'][$i] = $random;
        }
        return $this->rollData;
    }

    public function output(): string
    {
        $rollData = $this->roll();
        $rollOutput = '';
        foreach ($rollData['dice'] as $die => $result) {
            if ($rollData['modifier'] == 0) {
                $rollOutput .= " $result;";
                continue;   
            }
            $rollOutput .= " $result+{$rollData['modifier']};";
        }
        return $rollOutput;
    }
}