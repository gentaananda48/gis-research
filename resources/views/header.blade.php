<header class="main-header">
	<!-- Logo -->
	<a href="{{ url('/') }}" class="logo">
		<!-- mini logo for sidebar mini 50x50 pixels -->
		<span class="logo-mini">BS</span>
		<!-- logo for regular state and mobile devices -->
		<span class="logo-lg">Boom Sprayer</span>
	</a>

	<!-- Header Navbar -->
	<nav class="navbar navbar-static-top" role="navigation">
		<!-- Sidebar toggle button-->
		<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
	   		<span class="sr-only">Toggle navigation</span>
		</a>

	 	<!-- Navbar Right Menu -->
	  	<div class="navbar-custom-menu">
			<ul class="nav navbar-nav">
				<!-- User Account Menu -->
				<li class="dropdown user user-menu">
		            <!-- Menu Toggle Button -->
		            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
		              	<!-- The user image in the navbar-->
		              	<img src="{{ url($profile->image) }}" class="user-image" alt="User Image">
		              	<!-- hidden-xs hides the username on small devices so only the image appears. -->
		              	<span class="hidden-xs">{{ $profile->name }}</span>
		            </a>
		            <ul class="dropdown-menu">
		              	<!-- The user image in the menu -->
		              	<li class="user-header">
		                	<img src="{{ url($profile->image) }}" class="img-circle" alt="User Image">
			                <p>
			                  	{{ $profile->name }}
			                  	<small>{{ $profile->roleName() }}</small>
			                </p>
		              	</li>

		              	<!-- Menu Footer-->
		              	<li class="user-footer">
			                <div class="pull-left">
			                  	<a href="{{ url('/myprofile') }}" class="btn btn-default btn-flat">Profile</a>
			                </div>
			                <div class="pull-right">
			                  	<a href="{{ url('/logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
		                        	SignOut
		                        </a>
		                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
		                            {{ csrf_field() }}
		                        </form>
			                </div>
		              	</li>
		            </ul>
				</li>
			</ul>
		</div>
	</nav>
</header>