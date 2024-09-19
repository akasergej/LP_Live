<?php

namespace LP\LPShipping\Model;

class LPExpressTerminalRepository implements \LP\LPShipping\Api\LPExpressTerminalRepositoryInterface
{
    /**
     * @var \LP\LPShipping\Model\LPExpressTerminalsFactory $_terminalsFactory
     */
    protected $_terminalsFactory;

    /**
     * @var \LP\LPShipping\Model\LPExpressTerminals $_terminalResource
     */
    protected $_terminalResource;

    /**
     * @var \LP\LPShipping\Model\ResourceModel\LPExpressTerminals\CollectionFactory $_terminalCollectionFactory
     */
    protected $_terminalCollectionFactory;

    /**
     * LPExpressTerminalRepository constructor.
     * @param \LP\LPShipping\Model\LPExpressTerminalsFactory $terminalsFactory
     * @param \LP\LPShipping\Model\ResourceModel\LPExpressTerminals $terminalResource
     * @param \LP\LPShipping\Model\ResourceModel\LPExpressTerminals\CollectionFactory $terminalCollectionFactory
     */
    public function __construct(
        \LP\LPShipping\Model\LPExpressTerminalsFactory                          $terminalsFactory,
        \LP\LPShipping\Model\ResourceModel\LPExpressTerminals                   $terminalResource,
        \LP\LPShipping\Model\ResourceModel\LPExpressTerminals\CollectionFactory $terminalCollectionFactory
    )
    {
        $this->_terminalsFactory = $terminalsFactory;
        $this->_terminalResource = $terminalResource;
        $this->_terminalCollectionFactory = $terminalCollectionFactory;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByTerminalId($terminalId)
    {
        /** @var \LP\LPShipping\Model\LPExpressTerminals $terminal */
        $terminal = $this->_terminalsFactory->create();
        $this->_terminalResource->load($terminal, $terminalId, 'terminal_id');

        if (!$terminal->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Unable to find terminal.')
            );
        }

        return $terminal;
    }

    public function getList(): array
    {
        $formattedTerminalList = [];

        // Terminal cities at top
        $topList = [
            'Vilnius',
            'Kaunas',
            'Klaipėda',
            'Šiauliai',
            'Panevežys',
            'Alytus',
            'Marijampolė',
            'Utena',
            'Telšiai',
            'Tauragė'
        ];

        /** @var \LP\LPShipping\Model\ResourceModel\LPExpressTerminals\Collection $terminalCollection */
        $terminalCollection = $this->_terminalCollectionFactory->create()->getItems();

        /** @var \LP\LPShipping\Model\LPExpressTerminals $terminal */
        foreach ($terminalCollection as $terminal) {
            $formattedTerminalList[$terminal->getCountryCode()][$terminal->getCity()][$terminal->getTerminalId()] =
                trim(sprintf('%s - %s', $terminal->getName(), $terminal->getAddress()));
        }
        if (empty($formattedTerminalList)) {
            return [];
        }

        // Sort terminals alphabetically
        foreach ($formattedTerminalList as $country => $list) {
            foreach ($list as $key => $value) {
                asort($formattedTerminalList[$country][$key], SORT_ASC);
            }
        }

        // Top sort cities
        $ordered = [];
        foreach ($topList as $key) {
            if (array_key_exists($key, $formattedTerminalList['LT'])) {
                $ordered['LT'][$key] = $formattedTerminalList['LT'][$key];
                // Unset top listed cities
                unset($formattedTerminalList['LT'][$key]);
            }
        }

        $collator = new \Collator('en_US');
        uksort($formattedTerminalList['LT'], function($a, $b) use ($collator) {
            return $collator->compare($a, $b);
        });
        $formattedTerminalList['LT'] = $ordered['LT'] + $formattedTerminalList['LT'];

        return $formattedTerminalList;
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\LP\LPShipping\Api\Data\LPExpressTerminalsInterface $terminal)
    {
        /** @var \LP\LPShipping\Model\LPExpressTerminals $terminal */
        $this->_terminalResource->save($terminal);

        return $terminal;
    }
}
