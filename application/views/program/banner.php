<!-- 引入bs-confirmation -->
<script src="/bower_components/bs-confirmation/bootstrap-confirmation.js"></script>
<!-- 引入css -->
<link rel="stylesheet" type="text/css" href="/assets/css/global.css">

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
    <ol class="breadcrumb">
      <li><a href="<?=base_url('dashboard/index')?>"><i class="fa fa-dashboard"></i> 首页</a></li>
      <li class="active"><?=$title?></li>
    </ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title"><?=$title?></h3>
				<div class="box-tools pull-right">
					<!-- <a class="btn btn-sm btn-primary" href="add">添加</a> -->
				</div><!-- /.box-tools -->
			</div><!-- /.box-header -->
			<div class="box-body">
				<table class="table table-hover table-striped table-bordered">
					<thead>
						<tr>
							<th>缩略图</th>
							<th>标题</th>
							<th>排序</th>
							<th>操作</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($result as $item):?>
							<tr>
								<td><img width="220" height="100" src="<?=$item->get('avatar')?>" class="popover-show" data-container="body" data-placement="bottom" data-toggle="popover" data-html="true" data-trigger="hover focus click" data-content="<img src='<?=$item->get('avatar')?>' />" /></td>
								<td><?=$item->get('title')?></td>
								<td><?=$item->get('paixu')?></td>
								<td>
                  <a type="button" class="btn btn-primary" href="edit?objectId=<?=$item->get('objectId')?>">修改</a>
                  <a type="button" class="btn btn-danger delete" href="delete?objectId=<?=$item->get('objectId')?>">删除</a>
                </td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
				<script type="text/javascript">
					$('.delete').confirmation({
						onConfirm: function() { },
						onCancel: function() { },
						href: function (e) {
							return $(e).attr('href');
						},
						title: '确定删除吗？',
						btnOkClass: 'btn btn-sm btn-danger btn-margin',
						btnCancelClass: 'btn btn-sm btn-default btn-margin',
						btnOkLabel: '删除',
						btnCancelLabel: '取消',
						placement: 'bottom'
					})
				</script>
			</div><!-- /.box-body -->
			<div class="box-footer">
			</div><!-- box-footer -->
		</div><!-- /.box -->
	</section>
	<!-- /.content -->
</div>
<script>
	$(function () { 
		$("[data-toggle='popover']").popover();
	});
</script>