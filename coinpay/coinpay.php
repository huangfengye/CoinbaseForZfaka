<?php
/**
 * File: coinbasepay.php
 * Functionality: coinbasepay -coinbasepay
 * Author: 黄枫叶
 * Date: 2021-08-7
 */
namespace Pay\coinpay;
use \Pay\notify;


class coinpay
{
	private $apiHost="https://api.commerce.coinbase.com/charges";
	private $paymethod ="coinpay";
	
	//处理请求
	public function pay($payconfig,$params)
	{
		try
		{
			
			$fees = (double)$payconfig['configure4'];//手续费费率  比如 0.05
			if($fees>0.00)
			{
				$price_amount =(double)$params['money'] * (1.00+$fees);// 价格 * （1 + 0.05）
			}
			else
			{
				$price_amount =(double)$params['money'];
			}
			
			$price_amount = sprintf('%.2f', $price_amount);// 只取小数点后两位

			$redirect_url = $params['weburl']. "/query/auto/{$params['orderid']}.html";  //同步地址  
			$cancel_url = $params['weburl']. "/query/auto/{$params['orderid']}.html";  //同步地址

			$config = [
                'name'=>$params['productname'],
                'description'=>$params['productname'].'需付款'.$price_amount.'元',
			    'pricing_type' => 'fixed_price',
				'local_price' => [
					'amount' =>  $price_amount,
					'currency' => 'CNY'
				],
				'metadata' => [
					'customer_id' =>  $params['orderid'],
					'customer_name' => $params['productname']
				],
				'redirect_url' =>$redirect_url,
				'cancel_url'=> $cancel_url
			];
			
			$header = array();
			$header[] = 'Content-Type:application/json';
			$header[] = 'X-CC-Api-Key:'.$payconfig['app_secret']; //APP key
			$header[] = 'X-CC-Version: 2018-03-22';
			

            $createOrderUrl = $this->apiHost;

			$ch = curl_init(); //使用curl请求
            curl_setopt($ch, CURLOPT_URL, $createOrderUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config));
            $coinpay_json = curl_exec($ch);
            curl_close($ch);
           
		    $coinpay_date=json_decode($coinpay_json,true);
			
			if(is_array($coinpay_date))
			{
				{
					$JumpUrl = $coinpay_date['data']['hosted_url'];
					$closetime = 6000;			
					$result = array('type'=>1,'subjump'=>0,'url'=>$JumpUrl,'paymethod'=>$this->paymethod,'payname'=>$payconfig['payname'],'overtime'=>$closetime,'money'=>$price_amount);
					return array('code'=>1,'msg'=>'success','data'=>$result);
				}			
			}else
			{
				return array('code'=>1001,'msg'=>"支付接口请求失败",'data'=>'');
			}
		} 
		catch (\Exception $e) 
		{
			return array('code'=>1000,'msg'=>$e->getMessage(),'data'=>'');
		}
	}
	
	
	//处理返回
	public function notify($payconfig)
	{
		$payload = file_get_contents( 'php://input' );
		
		$sig    = $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'];
		
		$secret = $payconfig['configure3'];
       
		$sig2 = hash_hmac( 'sha256', $payload, $secret );
		
		if ( ! empty( $payload ) && ($sig === $sig2) ) 
		{
			$data       = json_decode( $payload, true );
			$event_data = $data['event']['data'];

	
			foreach ($event_data['payments'] as $payment) {
				if (strtolower($payment['status']) === 'confirmed') {
					$return_pay_amount = $payment['value']['local']['amount'];
					$return_currency=$payment['value']['local']['currency'];
					$return_status=strtolower($payment['status']);
				}
			}
			if($return_currency !== 'CNY')
			{
				return 'error|Notify: Wrong currency:'.$return_currency;
			}
			else
			{
				$return_merchant_order_id = $event_data['metadata']['customer_id'];//商户订单号
                $tradeid = $event_data['code'];

				if($return_status === 'confirmed')
				{
					$config = array('paymethod' => $this->paymethod, 'tradeid' => $tradeid, 'paymoney' => $return_pay_amount, 'orderid'=>$return_merchant_order_id );
					$notify = new \Pay\notify();
					$data = $notify->run($config);
					if ($data['code'] > 1) {
						return 'error|Notify: ' . $data['msg'] ;
					} 
					else 
					{
						return 'success';
					}
				}
				else
				{
					return 'error|Notify: status not completed :'.$return_status;
				}
		
			}	
		}
		else { //合法的数据
            //业务处理
			return 'error|Notify: auth Fail'.$payload.'='.$sig.'='.$sig2.'='.$XCCWebhookSignature;
        }
	}


}