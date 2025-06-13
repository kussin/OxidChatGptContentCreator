<?php
namespace Kussin\ChatGpt\Cron;

use Kussin\ChatGpt\Traits\ArticleDataEnhancerTrait;
use Kussin\ChatGpt\Traits\ChatGPTClientTrait;
use Kussin\ChatGpt\Traits\LanguageTrait;
use Kussin\ChatGpt\Traits\LoggerTrait;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Article;
use Kussin\ChatGpt\Traits\WeightGeneratorTrait;


class ChatGPTWeight extends FrontendController
{
    use ArticleDataEnhancerTrait;
    use ChatGPTClientTrait;
    use LanguageTrait;
    use LoggerTrait;
    use WeightGeneratorTrait;
    use LoggerTrait;

    protected $_aResponse = [
        'success'           => true,
        'generated'         => 0,
        'query'             => '',
        'class'             => __CLASS__,
        'validation_errors' => [],
        'system_errors'     => [],
    ];

    public function render()
    {
        $this->processArticles();
        $oLogger = oxNew(\Wmdk\Wh1CoreModifications\Core\WmdkLogger::class);
        $oLogger->output($this->_aResponse);
        exit;
    }
    protected function processArticles(): void
    {
        foreach ($this->getZeroWeightArticles() as $aRow) {

            $oArticle = oxNew(Article::class);
            if (!$oArticle->load($aRow['OXID'])) {
                $this->_aResponse['system_errors'][] = 'Cannot load ' . $aRow['OXID'];
                continue;
            }

            try {
                $this->_oArticle = $oArticle;
                $this->kussinchatgptweight();

                $this->_aResponse['generated']++;
            } catch (\Throwable $e) {
                $this->_aResponse['system_errors'][] = $e->getMessage();
            }
        }
    }

    protected function buildSelectQuery(): string
    {
        $fromDate = Registry::getConfig()->getConfigParam('sKussinChatGptWeightQueryTimestamp');
        $quotedDate = DatabaseProvider::getDb()->quote($fromDate);

        $sQuery = "
                    SELECT OXID, OXARTNUM, OXTITLE, OXWEIGHT, OXTIMESTAMP
                      FROM oxarticles
                     WHERE (OXWEIGHT <= 0 OR OXWEIGHT IS NULL)
                       AND OXTIMESTAMP >= {$quotedDate}
                    ";

        return $sQuery;
    }


    protected function getZeroWeightArticles(): array
    {
        $oDb    = DatabaseProvider::getDb();
        $sQuery = $this->buildSelectQuery();

        $this->_aResponse['query'] = $sQuery;

        $oDb->setFetchMode(\OxidEsales\EshopCommunity\Core\Database\Adapter\DatabaseInterface::FETCH_MODE_ASSOC);
        return $oDb->getAll($sQuery);
    }
    private function _kussinLoadArticle()
    {
        if ($this->_oArticle === null) {
            $this->_oArticle = oxNew(Article::class);
            $this->_oArticle->load($this->getEditObjectId());
        }

        return $this->_oArticle;
    }

}
