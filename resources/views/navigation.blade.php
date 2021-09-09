<aside class="main-sidebar">
	<!-- sidebar: style can be found in sidebar.less -->
	<section class="sidebar">
		<!-- Sidebar user panel (optional) -->
		<div class="user-panel">
			<!-- <div class="pull-left image">
		    	<img src="{{ $profile->image }}" class="img-circle" alt="User Image">
			</div>
			<div class="pull-left info">
		   		<p>{{ $profile->name }}</p>
		 		<small class="text-muted">{{ $profile->roleName() }}</small>
			</div> -->
		</div>
	   	<!-- Sidebar Menu -->
	   	<ul class="sidebar-menu" data-widget="tree">
			<li class="header">MENU</li>
			<!-- Optionally, you can add icons to the links -->
			<?php print $menu; ?>
		</ul>
	   	
		
	    <!-- /.sidebar-menu -->
	</section>
    <!-- /.sidebar -->
</aside>