<?php

namespace LP\LPShipping\Api;

interface LPExpressTerminalRepositoryInterface
{
    /**
     * @param int $terminalId
     * @return \LP\LPShipping\Api\Data\LPExpressTerminalsInterface
     */
    public function getByTerminalId($terminalId);

    /**
     * @return array
     */
    public function getList();

    /**
     * @param Data\LPExpressTerminalsInterface $terminal
     * @return \LP\LPShipping\Api\Data\LPExpressTerminalsInterface
     */
    public function save(\LP\LPShipping\Api\Data\LPExpressTerminalsInterface $terminal);
}
