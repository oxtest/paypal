<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Action\Data;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData class.
 */
class OrderRefundActionDataTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * Tests setting parameters from request
     */
    public function testSettingParameters_FromRequest()
    {
        $sTransactionId = '123456';
        $sAmount = '59.92';
        $sType = 'Full';

        $aParams = array(
            'transaction_id' => $sTransactionId,
            'refund_amount'  => $sAmount,
            'refund_type'    => $sType,
        );
        $oRequest = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getPost' => $aParams));

        $oOrder = $this->_getOrder();

        $oActionData = new \OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData($oRequest, $oOrder);

        $this->assertEquals($sTransactionId, $oActionData->getTransactionId());
        $this->assertEquals($sAmount, $oActionData->getAmount());
        $this->assertEquals($sType, $oActionData->getType());
    }

    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrderPayment()
    {
        $sRemainingRefundSum = 59.67;

        $oPayment = $this->_createStub('oePayPalPayPalOrderPayment', array('getRemainingRefundAmount' => $sRemainingRefundSum));
        $oRequest = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getPost' => array()));

        $oOrder = $this->_getOrder();

        $oActionData = $this->getMock(\OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::class, array('getPaymentBeingRefunded'), array($oRequest, $oOrder));
        $oActionData->expects($this->any())->method('getPaymentBeingRefunded')->will($this->returnValue($oPayment));

        $this->assertEquals($sRemainingRefundSum, $oActionData->getAmount());
    }

    /**
     * Test loading of payment by transaction id
     */
    public function testGetPaymentBeingRefunded_LoadedByTransactionId_TransactionIdSet()
    {
        $sTransactionId = 'test_transId';

        $oPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oPayment->setTransactionId($sTransactionId);
        $oPayment->setOrderId('_testOrderId');
        $oPayment->save();

        $aParams = array('transaction_id' => $sTransactionId);
        $oRequest = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getPost' => $aParams));

        $oOrder = $this->_getOrder();

        $oActionData = new \OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData($oRequest, $oOrder);

        $oPayment = $oActionData->getPaymentBeingRefunded();

        $this->assertEquals($sTransactionId, $oPayment->getTransactionId());
    }

    /**
     *  Returns Request object with given parameters
     *
     * @param $aParams
     *
     * @return mixed
     */
    protected function _getRequest($aParams)
    {
        $oRequest = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getGet' => $aParams));

        return $oRequest;
    }

    /**
     *
     */
    protected function _getOrder()
    {
        $oOrder = new \OxidEsales\PayPalModule\Model\PayPalOrder();

        return $oOrder;
    }
}