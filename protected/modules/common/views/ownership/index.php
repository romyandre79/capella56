<?php $this->widget('Form',	array('menuname'=>$this->menuname,
	'idfield'=>'ownershipid',
	'formtype'=>'master',
	'url'=>Yii::app()->createUrl('common/ownership/index',array('grid'=>true)),
	'saveurl'=>Yii::app()->createUrl('common/ownership/save',array('grid'=>true)),
	'updateurl'=>Yii::app()->createUrl('common/ownership/save',array('grid'=>true)),
	'destroyurl'=>Yii::app()->createUrl('common/ownership/purge',array('grid'=>true)),
	'uploadurl'=>Yii::app()->createUrl('common/ownership/upload'),
	'downpdf'=>Yii::app()->createUrl('common/ownership/downpdf'),
	'downxls'=>Yii::app()->createUrl('common/ownership/downxls'),
	'downdoc'=>Yii::app()->createUrl('common/ownership/downdoc'),
	'columns'=>"
		{
			field:'ownershipid',
			title: localStorage.getItem('catalogownershipid'), 
			sortable:'true',
			width:'50px',
			formatter: function(value,row,index){
				return value;
			}
		},
		{
			field:'ownershipname',
			title: localStorage.getItem('catalogownershipname'), 
			editor:'text',
			width:'150px',
			sortable:'true',
			formatter: function(value,row,index){
				return value;
			}
		},
		{
			field:'recordstatus',title: localStorage.getItem('catalogrecordstatus'),
			align:'center',
			width:'50px',
			editor:{type:'checkbox',options:{on:'1',off:'0'}},
			sortable:'true',
			formatter: function(value,row,index){
				if (value == 1){
					return '<img src=\"".Yii::app()->request->baseUrl.'/images/ok.png'."\"></img>';
				} else {
					return '';
				}
			}
		}",
	'searchfield'=> array ('ownershipid','ownershipname')
));