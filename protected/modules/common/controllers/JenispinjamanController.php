<?php
class JenispinjamanController extends Controller {
	public $menuname = 'jenispinjaman';
	public function actionIndex() {
		parent::actionIndex();
		if(isset($_GET['grid']))
			echo $this->search();
		else
			$this->renderPartial('index',array());
	}
	public function search() {
		header('Content-Type: application/json');
		$jenispinjamanid = GetSearchText(array('POST','Q'),'jenispinjamanid');
		$namapinjaman = GetSearchText(array('POST','Q'),'namapinjaman');
		$page = GetSearchText(array('POST'),'page',1,'int');
		$rows = GetSearchText(array('POST'),'rows',10,'int');
		$sort = GetSearchText(array('POST'),'sort','jenispinjamanid','int');
		$order = GetSearchText(array('POST'),'order','desc','int');
		$offset = ($page-1) * $rows;
		$result = array();
		$row = array();
		$dependency = new CDbCacheDependency('SELECT MAX(updatedate) FROM jenispinjaman');
		if (!isset($_GET['combo'])) {
			$cmd = Yii::app()->db->cache(1000,$dependency)->createCommand()
				->select('count(1) as total')	
				->from('jenispinjaman t')
				->where('(jenispinjamanid like :jenispinjamanid) and (namapinjaman like :namapinjaman)',
					array(':jenispinjamanid'=>$jenispinjamanid,':namapinjaman'=>$namapinjaman))
				->queryScalar();
		}
		else  {
			$cmd = Yii::app()->db->cache(1000,$dependency)->createCommand()
				->select('count(1) as total')	
				->from('jenispinjaman t')
				->where('((jenispinjamanid like :jenispinjamanid) or (namapinjaman like :namapinjaman)) and t.recordstatus=1',
					array(':jenispinjamanid'=>$jenispinjamanid,':namapinjaman'=>$namapinjaman))
				->queryScalar();
		}
		$result['total'] = $cmd;
		if (!isset($_GET['combo'])) {
			$cmd = Yii::app()->db->cache(1000,$dependency)->createCommand()
				->select()	
				->from('jenispinjaman t')
				->where('(jenispinjamanid like :jenispinjamanid) and (namapinjaman like :namapinjaman)',
					array(':jenispinjamanid'=>$jenispinjamanid,':namapinjaman'=>$namapinjaman))
				->offset($offset)
				->limit($rows)
				->order($sort.' '.$order)
				->queryAll();
		}
		else {
			$cmd = Yii::app()->db->cache(1000,$dependency)->createCommand()
				->select()	
				->from('jenispinjaman t')
				->where('((jenispinjamanid like :jenispinjamanid) or (namapinjaman like :namapinjaman)) and t.recordstatus=1',
					array(':jenispinjamanid'=>$jenispinjamanid,':namapinjaman'=>$namapinjaman))
				->order($sort.' '.$order)
				->queryAll();
		}
		foreach($cmd as $data) {	
			$row[] = array(
				'jenispinjamanid'=>$data['jenispinjamanid'],
				'namapinjaman'=>$data['namapinjaman'],
				'jumlah'=>Yii::app()->format->formatNumber($data['jumlah']),
				'bunga'=>Yii::app()->format->formatNumber($data['bunga']),
				'fixed'=>$data['fixed'],
				'tenor'=>$data['tenor'],
				'biayaadm'=>Yii::app()->format->formatNumber($data['biayaadm']),
				'simpokok'=>Yii::app()->format->formatNumber($data['simpokok']),
				'biayamaterai'=>Yii::app()->format->formatNumber($data['biayamaterai']),
				'biayaasuransi'=>Yii::app()->format->formatNumber($data['biayaasuransi']),
				'maxday'=>$data['maxday'],
				'isauto'=>$data['isauto'],
				'recordstatus'=>$data['recordstatus'],
			);
		}
		$result=array_merge($result,array('rows'=>$row));
		return CJSON::encode($result);
	}
	private function ModifyData($connection,$arraydata) {
		$id = (isset($arraydata[0])?$arraydata[0]:'');
		if ($id == '') {
			$sql = 'call Insertjenispinjaman(:vnamapinjaman,:vjumlah,:vbunga,:vfixed,:vtenor,:vbiayaadm,:vsimpokok,:vbiayamaterai,:vbiayaasuransi,:vmaxday,:visauto,:vrecordstatus,:vdatauser)';
			$command=$connection->createCommand($sql);
		}
		else {
			$sql = 'call Updatejenispinjaman(:vid,:vnamapinjaman,:vjumlah,:vbunga,:vfixed,:vtenor,:vbiayaadm,:vsimpokok,:vbiayamaterai,:vbiayaasuransi,:vmaxday,:visauto,:vrecordstatus,:vdatauser)';
			$command=$connection->createCommand($sql);
			$command->bindvalue(':vid',$arraydata[0],PDO::PARAM_STR);
			$this->DeleteLock($this->menuname, $arraydata[0]);
		}
		$command->bindvalue(':vnamapinjaman',$arraydata[1],PDO::PARAM_STR);
		$command->bindvalue(':vjumlah',$arraydata[2],PDO::PARAM_STR);
		$command->bindvalue(':vbunga',$arraydata[3],PDO::PARAM_STR);
		$command->bindvalue(':vfixed',$arraydata[4],PDO::PARAM_STR);
		$command->bindvalue(':vtenor',$arraydata[5],PDO::PARAM_STR);
		$command->bindvalue(':vbiayaadm',$arraydata[6],PDO::PARAM_STR);
		$command->bindvalue(':vsimpokok',$arraydata[7],PDO::PARAM_STR);
		$command->bindvalue(':vbiayamaterai',$arraydata[8],PDO::PARAM_STR);
		$command->bindvalue(':vbiayaasuransi',$arraydata[9],PDO::PARAM_STR);
		$command->bindvalue(':vmaxday',$arraydata[10],PDO::PARAM_STR);
		$command->bindvalue(':visauto',$arraydata[11],PDO::PARAM_STR);
		$command->bindvalue(':vrecordstatus',$arraydata[12],PDO::PARAM_STR);
		$command->bindvalue(':vdatauser', GetUserPC(),PDO::PARAM_STR);
		$command->execute();			
	}
	public function actionUpload() {
		parent::actionUpload();
		$target_file = dirname('__FILES__').'/uploads/' . basename($_FILES["file-jenispinjaman"]["name"]);
		if (move_uploaded_file($_FILES["file-jenispinjaman"]["tmp_name"], $target_file)) {
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load($target_file);
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$highestRow = $objWorksheet->getHighestRow(); 
			$highestColumn = $objWorksheet->getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
			$connection=Yii::app()->db;
			$transaction=$connection->beginTransaction();
			try {
				for ($row = 2; $row <= $highestRow; ++$row) {
					$id = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
					$vnamapinjaman = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
					$vjumlah = $objWorksheet->getCellByColumnAndRow(2, $row)->getValue();
					$vbunga = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();
					$vfixed = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
					$vtenor = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$vbiayaadm = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$vsimpokok = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$vbiayamaterai = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$vbiayaasuransi = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$vmaxday = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
					$visauto = $objWorksheet->getCellByColumnAndRow(6, $row)->getValue();
					$recordstatus = $objWorksheet->getCellByColumnAndRow(7, $row)->getValue();
					$this->ModifyData($connection,array($id,$vnamapinjaman,$vjumlah,$vbunga,$vfixed,$vtenor,$vbiayaadm,$vsimpokok,$vbiayamaterai,$vbiayaasuransi,$vmaxday,$visauto,$recordstatus));
				}
				$transaction->commit();
				GetMessage(false,'insertsuccess');
			}
			catch (CDbException $e) {
				$transaction->rollBack();
				GetMessage(true,'Line: '.$row.' ==> '.implode(" ",$e->errorInfo));
			}
    }
	}
	public function actionSave() {
		parent::actionWrite();
		$connection=Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->ModifyData($connection,array((isset($_POST['jenispinjamanid'])?$_POST['jenispinjamanid']:''),
				$_POST['namapinjaman'],
				$_POST['jumlah'],
				$_POST['bunga'],
				$_POST['fixed'],
				$_POST['tenor'],
				$_POST['biayaadm'],
				$_POST['simpokok'],
				$_POST['biayamaterai'],
				$_POST['biayaasuransi'],
				$_POST['maxday'],
				$_POST['isauto'],
				$_POST['recordstatus']));
			$transaction->commit();
			GetMessage(false,'insertsuccess');
		}
		catch (CDbException $e) {
			$transaction->rollBack();
			GetMessage(true,implode(" ",$e->errorInfo));
		}
	}
	public function actionPurge() {
		parent::actionPurge();
		if (isset($_POST['id'])) {
			$id=$_POST['id'];
			$connection=Yii::app()->db;
			$transaction=$connection->beginTransaction();
			try {
				$sql = 'call Purgejenispinjaman(:vid,:vdatauser)';
				$command=$connection->createCommand($sql);
				$command->bindvalue(':vid',$id,PDO::PARAM_STR);
				$command->bindvalue(':vdatauser',GetUserPC(),PDO::PARAM_STR);
				$command->execute();
				$transaction->commit();
				GetMessage(false,'insertsuccess');
			}
			catch (CDbException $e) {
				$transaction->rollBack();
				GetMessage(true,implode(" ",$e->errorInfo));
			}
		}
		else {
			GetMessage(true,'chooseone');
		}
	}
	protected function actionDataPrint() {
		parent::actionDataPrint();
		$this->dataprint['namapinjaman'] = GetSearchText(array('GET'),'namapinjaman');
		$id = GetSearchText(array('GET'),'id');
		if ($id != '%%') {
			$this->dataprint['id'] = $id;
		} else {
			$this->dataprint['id'] = GetSearchText(array('GET'),'jenispinjamanid');
		}
		$this->dataprint['titleid'] = GetCatalog('id');
		$this->dataprint['titlenamapinjaman'] = GetCatalog('namapinjaman');
		$this->dataprint['titlerecordstatus'] = GetCatalog('recordstatus');
  }
}