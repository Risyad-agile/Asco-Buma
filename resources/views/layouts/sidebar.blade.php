      <!-- Main Sidebar Container -->
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('login') }}" class="brand-link">
          <img src="{{asset('images/agile_logo.png')}}" alt="KidswaFarma" class="brand-image img-circle elevation-3"
               style="opacity: .8">
            @guest
              <span class="brand-text font-weight-light">ASRI Dashboard</span>
            @else
              <span class="brand-text font-weight-light">{{ Auth::user()->company->comp_name}}</span>
            @endguest
        </a>
  
        <!-- Sidebar -->
        <div class="sidebar">
          <!-- Sidebar user panel (optional) -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image"> 
              <img src="{{asset('images/agile_logo.png')}}" class="img-circle elevation-2" alt="User">
            </div>
            <div class="info">
              <a href="#" class="d-block"><span class="text-muted">{{ Auth::user()->name }}</span></a>
            </div>
          </div>
  
          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              @hasanyrole('superadmin') <!-- ====================== SUPERADMIN ===================================== -->  
              <li class="nav-header">MANAGE</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>
                    File
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('companies.browse')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Company</p>
                    </a>
                  </li> 
                  <li class="nav-item">
                    <a href="{{route('organizations.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Organization</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('location.browse')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Location</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fa fa-cubes"></i>
                  <p>
                    Account
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('accountstyle.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account Style List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('accountstyles.company.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Company Account Style</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('accountstyles.import.file.upload')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Import Account Style XLS</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('account.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Data Capture</p>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole
              @hasrole('user') <!-- ====================== USER ===================================== --> 
              <li class="nav-header">MANAGE</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p>
                    File
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview"> 
                  <li class="nav-item">
                    <a href="{{route('location.browse')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Location</p> 
                    </a>
                  </li>
                </ul> 
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('partner.accountstyles.company.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account Style</p>
                    </a>
                  </li> 
                </ul>
              </li> 
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-edit"></i>
                  <p>Tasks
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">                   
                  <li class="nav-item">
                    <a href="{{route('create.task.listE')}}" class="nav-link" style="padding-left: 40px">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Create and List</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">                   
                  <li class="nav-item">
                    <a href="{{route('create.task.listS')}}" class="nav-link" style="padding-left: 40px">
                      <i class="far fa-circle nav-icon text-success"></i> 
                      <p>Create and List CSR</p>
                    </a>
                  </li>
                </ul>
              </li>        
              @endrole
              @hasrole('supervisor') <!-- ====================== SUPERVISOR ===================================== --> 
              <li class="nav-header">MANAGE</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-building"></i>
                  <p>
                    File
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview"> 
                  <li class="nav-item">
                    <a href="{{route('partner.location.list')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Location</p> 
                    </a>
                  </li>                
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('partner.accountstyles.company.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account Style</p>
                    </a>
                  </li> 
                </ul>
              </li>               
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-edit"></i>
                  <p>
                    Tasks
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('verify.task.listE')}}" class="nav-link" style="padding-left: 40px"> 
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Verification</p>
                    </a>
                  </li>
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('verify.task.listS')}}" class="nav-link" style="padding-left: 40px"> 
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Verification CSR</p>
                    </a>
                  </li>
                </ul>
              </li>
               
              @endrole
              @hasrole('manager')
              <li class="nav-header">MANAGE</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-building"></i>
                  <p>File
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview"> 
                  <li class="nav-item">
                    <a href="{{route('partner.location.list')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Location</p> 
                    </a>
                  </li> 
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('partner.accountstyles.company.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account Style</p>
                    </a>
                  </li> 
                </ul>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('customFactor.browse')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Custom Factor</p>
                    </a>
                  </li> 
                </ul>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-edit"></i>
                  <p>
                    Tasks
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('approve.task.listE')}}" class="nav-link" style="padding-left: 40px"> 
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Approve and Submit</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('approve.task.listS')}}" class="nav-link" style="padding-left: 40px"> 
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Approve and Submit CSR</p>
                    </a>
                  </li>
                </ul>
              </li> 
              <li class="nav-header">ADMIN</li> 
              @endrole
              @hasanyrole("superadmin|manager|supervisor|user|admin")
              <li class="nav-item">
                <a href="{{route('partner.user.changepassword.index')}}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Change Password</p>
                </a>
              </li>
              @endrole
              @hasanyrole('superadmin|agile-manager')  
              <li class="nav-header">MONITOR</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-business-time"></i>
                  <p>
                    Jobs & Task
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('connector_job.browse')}}" class="nav-link" style="padding-left: 40px">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Job List</p>
                    </a>
                  </li> 
                  <li class="nav-item">
                    <a href="{{route('task.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Task List</p>
                    </a>
                  </li> 
                </ul>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon far fa-file-alt"></i>
                  <p>
                    Syncronize 
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('syncronize.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Envizi Data</p>
                    </a>
                  </li>
                </ul>
              </li>    
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon far fa-file-alt"></i>
                  <p>
                    Receive & Retrieve 
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('processdatalog.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Receive Data</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Retrieve Data</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.error.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-danger"></i>
                      <p>Error Process Log</p>
                    </a>
                  </li>
                </ul>
              </li> 
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon far fa-file-alt"></i>
                  <p>
                    Upload File 
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('syncronize.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Task List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('syncronize.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Create Upload Task</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Check Upload Task</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.error.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Approve Upload Task</p>
                    </a>
                  </li>
                </ul>
              </li> 
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fa fa-cubes"></i>
                  <p>
                    Account Style
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('accountstyle.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>List</p>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole
  
              {{-- @hasanyrole('superadmin|manager')
              <li class="nav-header">REPORTS</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fa fa-cubes"></i>
                  <p>
                    Account
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Mapping</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-invoice-dollar	"></i>
                  <p>
                    Sustainability
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>ESG</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Emission</p>
                    </a>
                  </li> 
                </ul>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-plane-departure"></i>
                  <p>
                    Migration
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Account</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>CSR</p>
                    </a>
                  </li> 
                </ul>
              </li>
              @endrole --}}

              @hasrole('superadmin')
              <li class="nav-header">ADMIN</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-cogs"></i>
                  <p>Settings Connector <i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"> 
                    <a href="{{route('connector_target.browse')}}" class="nav-link" style="padding-left: 40px">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Destination Connector</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('connector_source.browse')}}" class="nav-link" style="padding-left: 40px">
                      <i class="far fa-circle nav-icon text-success"></i>
                      <p>Source Connector</p>
                    </a>
                  </li> 
                </ul>
              </li>
              <li class="nav-item has-treeview"> 
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-user-lock"></i>
                  <p>
                    API Registration 
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('users.api.token.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Create API Token</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('users.registration.list')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>List Requested Token</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Approval List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('processdatalog.error.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon text-danger"></i>
                      <p>Rejected List</p>
                    </a>
                  </li>
                </ul>
              </li>  
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fa fa-wrench"></i>
                  <p>
                    Users
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="{{route('users.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>User List</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="{{route('userright.index')}}" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>Access Level</p>
                    </a>
                  </li> 
                </ul>
              </li>
              @endrole
              @hasanyrole('agile-admin|agile-manager')
              <li class="nav-header">UTILITY</li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  {{-- <i class="nav-icon far fa-image"></i> --}}
                  <i class="nav-icon 	fas fa-user-shield"></i>
                  <p>
                    User
                    <i class="fas fa-angle-left right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-circle nav-icon"></i>
                      <p>User Account</p>
                    </a>
                  </li>
                </ul>
              </li>
              @endrole
              <li class="nav-item">
                <a class="nav-link" href="{{ route('logout')}}"
                  data-toggle="tooltip" data-placement="left" title="Sign Out"
                  onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();">
                  <i class="nav-icon fas fa-sign-out-alt"></i>
                  <p>Sign Out</p>
                </a>
              </li>
            </ul>
          </nav>
          <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
      </aside>
  