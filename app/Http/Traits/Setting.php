<?php

namespace App\Http\Traits;

use Throwable;
use Carbon\Carbon;
use App\Models\LogWeb;
use Illuminate\Http\Request;
use App\Http\Traits\CryptAES;
use App\Jobs\ProcessHitUrlLPR;
use App\Http\Traits\GibberishAES;
use App\Jobs\ProcessHitUrlLPROut;
use App\Jobs\ProcessHitUrlMaster;
use App\Jobs\ProcessHitUrlMember;
use Illuminate\Http\UploadedFile;
use App\Jobs\ProcessHitSkyVoucher;
use App\Jobs\ProcessHitUrlPayment;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessHitUrlTrafficIn;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessHitUrlTrafficOut;
use App\Jobs\ProcessHitUrlPaymentMember;
use App\Http\Controllers\IndexController;

trait Setting
{
	use CryptAES;
	protected $development = false;
	protected $limit = 100;
	protected $retry = 10;
	function getParameter($code)
	{
		$params = DB::table('m_parameter_t')
			->where('kode', $code)
			->select('nilai')->first();
		if ($params != null) {
			return $params->nilai;
		} else {
			return false;
		}
		// $query = "select nilai from m_parameter_t where kode='" . $code . "'";
		// $parameter = DB::select($query);
		// return $parameter[0]->nilai;
	}
	function getPosId()
	{
		$query = "select max(pos_id) pos_id from M_Pos_T with (nolock) where Job='MP' and flag_id=1";
		$master = DB::select($query);
		$logweb_posid = $master[0]->pos_id;
		return $logweb_posid;
	}
	function getManless()
	{
		$query = "select id from sec_user with (nolock) where name='MANLESS'";
		$master = DB::select($query);
		$logweb_petugasid = $master[0]->id;
		return $logweb_petugasid;
	}
	function _getEncKey()
	{
		return $this->getParameter("DASHBOARD_CRYPTKEY");
		// $query = "select isnull(partnerkey,'') partnerkey from sys_payment_t with (nolock) where payment_id=9 ";
		// $data = DB::select($query);
		// return $data[0]->partnerkey;
	}
	function _getLocation()
	{
		$query = "select nama from m_setup_t with (nolock) ";
		$data = DB::select($query);
		return $data[0]->nama;
	}

	function _getSetup()
	{
		return DB::table('m_setup_t')->first();
	}

	function is_connected($url)
	{
		$connected = @fsockopen($url, 80);
		//website, port  (try 80 or 443)
		if ($connected) {
			$is_conn = true; //action when connected
			fclose($connected);
		} else {
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	}

	function _requestUrl($url, $nota = null, $logfrom, $jenis)
	{ 
			$query = "INSERT INTO t_logweb_t(logdata,barcode,logfrom,no_nota,dateadd,est_tglkeluar,est_nilaiparkir,est_service,est_denda,pos_id,petugas_id,jenis) VALUES(
				'" . $url . "',
				null,
				'" . $logfrom . "',
				'" . $nota . "',
				GETDATE(),
				null,
				0,
				0,
				0,
				0,
				0,
				'" . $jenis . "'
			)";
			DB::statement($query);
			// LogWeb::create([
			// 	'logdata' => $url,
			// 	'barcode' => null,
			// 	'logfrom' => $logfrom,
			// 	'no_nota' => $nota,
			// 	'dateadd' => date("Y-m-d H:i:s.v"),
			// 	'est_tglkeluar' => null,
			// 	'est_nilaiparkir' => 0,
			// 	'est_service' => 0,
			// 	'est_denda' => 0,
			// 	'pos_id' => 0,
			// 	'petugas_id' => 0,
			// 	'jenis' => $jenis
			// ]);
		
	}
	function _responseDashboard($title, $nonota = null, Request $request, Throwable $th)
	{
		$logweb_posid = $this->getPosId();
		$logweb_petugasid = $this->getManless();
		DB::table('t_logweb_t')->insert([
			'logdata' => $th->getMessage(),
			'barcode' => null,
			'logfrom' => 'errorPaymentMember',
			'no_nota' => $nonota,
			'dateadd' => date("Y-m-d H:i:s"),
			'est_nilaiparkir' => 0,
			'est_service' => 0,
			'est_denda' => 0,
			'pos_id' => $logweb_posid,
			'petugas_id' => $logweb_petugasid,
			'jenis' => 99
		]);
		$this->urlFailed($request, $th);
		return response()->json(['error' => 'not connected']);
	}

	function getSendMasterData($traffic, $title = null)
	{
		$key = $this->_getEncKey();
		$parameter = $key . \Carbon\Carbon::now()->format('ymd');
		$username = $this->development ? 'rok' : $this->getParameter('DASHBOARD_USERNAME');
		$password = $this->development ? '+0FTZjcwjjBsa3uYcGRBg==::q1Y4LpCGz0QhgaV7Sz1HdQ==::9' : $this->encrypt($this->getParameter('DASHBOARD_PASSWORD'), $key);
		$array['userName'] = $username;
		$array['password'] = $password;
		$array['rows'] = 1;
		$json = [];
		if (is_null($traffic)) {
			DB::table('t_logweb_t')->insert([
				'logdata' => $title . ' pada id tersebut tidak tersedia',
				'barcode' => null,
				'logfrom' => 'errorMaster' . $title,
				'no_nota' => null,
				'dateadd' => date("Y-m-d H:i:s"),
				'est_nilaiparkir' => 0,
				'est_service' => 0,
				'est_denda' => 0,
				'jenis' => 99
			]);
			return ['error' => 'master data not found'];
		}
		foreach ($traffic as $key => $value) {
			$json[$key] = $value;
		}
		$array['data'][] = $json;
		$senddata = $array;
		$setup = $this->_getSetup();
		DB::table('t_logweb_t')->insert([
			'logdata' => json_encode($senddata),
			'barcode' => null,
			'logfrom' => 'InternalDashboardDataMaster' . $title,
			'no_nota' => '',
			'dateadd' => date("Y-m-d H:i:s"),
			'est_tglkeluar' => null,
			'est_nilaiparkir' => 0,
			'est_service' => 0,
			'est_denda' => 0,
			'pos_id' => 0,
			'petugas_id' => 0
		]);
		$sendjson = json_encode($senddata);
		$sendRequest = [];
		$sendRequest['locationID'] = $setup->Kode_Lokasi;
		$sendRequest['encode'] = 1;
		$sendRequest['timestamp'] = \Carbon\Carbon::now()->format('Y/m/d H:i:s');
		$sendRequest['senddata'] = $this->encrypt($sendjson, $parameter);
		return ['data' => $sendRequest];
	}
	function sendRequestMaster(Request $request, $urlforward, $req, $title, $id = null)
	{
		$logweb_posid = $this->getPosId();
		$logweb_petugasid = $this->getManless();
		if (count($req) <= 0) {
			DB::table('t_logweb_t')->insert([
				'logdata' => $title . ' pada id tersebut tidak tersedia',
				'barcode' => null,
				'logfrom' => 'errorMaster' . $title,
				'no_nota' => null,
				'dateadd' => date("Y-m-d H:i:s"),
				'est_nilaiparkir' => 0,
				'est_service' => 0,
				'est_denda' => 0,
				'pos_id' => $logweb_posid,
				'petugas_id' => $logweb_petugasid,
				'jenis' => 103
			]);
			return ['error' => 'master data not found'];
		}
		try {
			$result = Http::retry($this->retry)->asJson()->post($urlforward, $req);
			$ambil = (string) $result;
			// $dec = GibberishAES::dec($ambil,$tanggalutc);
			$this->_requestUrl($request, null, 'RequestMaster' . $title, 103);
			return json_decode($ambil);
		} catch (\Throwable $th) {
			$this->_responseRequest($th->getMessage(), null, 'Response' . $title . 'Error', 103);
			$this->urlFailed($request, $th);
			DB::table('M_' . $title . '_T')->where($title . '_ID', $id)->update(['Flag_ID' => 109]);
			return ['error' => 'Url Not Connected'];
		}
	}
	function _responseRequest($logdata, $nonota, $logfrom, $jenis)
	{
		$logweb_posid = $this->getPosId();
		$logweb_petugasid = $this->getManless();
		$query = "INSERT INTO t_logweb_t(logdata,barcode,logfrom,no_nota,dateadd,est_tglkeluar,est_nilaiparkir,est_service,est_denda,pos_id,petugas_id,jenis) VALUES(
			'" . $logdata . "',
			null,
			'" . $logfrom . "',
			'" . $nonota . "',
			GETDATE(),
			null,
			0,
			0,
			0,
			0,
			0,
			'" . $jenis . "'
		)";
		DB::statement($query);

		// DB::table('t_logweb_t')->insert([
		// 	'logdata' => $logdata,
		// 	'barcode' => null,
		// 	'logfrom' => $logfrom,
		// 	'no_nota' => $nonota,
		// 	'dateadd' => date("Y-m-d H:i:s"),
		// 	'est_nilaiparkir' => 0,
		// 	'est_service' => 0,
		// 	'est_denda' => 0,
		// 	'pos_id' => $logweb_posid,
		// 	'petugas_id' => $logweb_petugasid,
		// 	'jenis' => $jenis
		// ]);
	}
	function convertToUrl($params)
	{
		$queryString = http_build_query(array_diff_key($params, ["url" => ""]));

		// Step 3: Concatenate the base URL with the query string
		$baseUrl = $params['url'];
		$fullUrl = $baseUrl . "/forwardrequest?" . urldecode($queryString);

		// Output the full URL
		return $fullUrl;
	}
	function addToLogMKK($datas, $status)
	{
		foreach ($datas as $key => $value) {
			DB::table('t_logmkk_t')->updateOrInsert(['no_nota' => $value], ['status_upload' => $status, 'tanggal_proses' => now()]);
		}
	}
}
