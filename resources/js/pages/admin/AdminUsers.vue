<template>
  	<div>
		<!-- breadcrumb -->
		<route-breadcrumb></route-breadcrumb>

		<div class="lg:flex justify-between items-center mb-6">
            <p class="text-2xl font-semibold mb-2 lg:mb-0">Usuarios</p>
            <Button size="large" @click="addModal=true" v-if="isWritePermitted"><Icon type="md-add" /> Agregar nuevo usuario</Button>
        </div>
		<!-- Users Table-->
		<section class="text-gray-700 w-full bg-white border rounded-lg p-4">
			<table class="min-w-full table-auto border rounded-lg">
				<thead class="text-left">
				<tr class="bg-gray-700">
					<th class="px-3 hidden md:block w-10 py-2"><span class="text-gray-300"></span></th>
					<th class="px-3 py-2"><span class="text-gray-300">Nombre</span></th>
					<th class="px-3 py-2 hidden md:table-cell"><span class="text-gray-300">Id</span></th>
					<th class="px-3 py-2"><span class="text-gray-300">Correo</span></th>
					<th class="px-3 py-2"><span class="text-gray-300">Rol</span></th>
					<th class="px-2 py-2 w-5" v-if="isUpdatePermitted || isDeletePermitted"><span class="text-gray-300">Accioness</span></th>
				</tr>
				</thead>
				<tbody class="bg-gray-200">
				<tr class="bg-white border-4 border-gray-200" v-for="(user, i) in getUsers" :key="i" v-if="getUsers.length">
					<td class="px-3 break-all py-2 flex flex-row items-center hidden md:table-cell">
						<img
							class="h-10 w-10 rounded-full object-cover "
							:src="(user.avatar) ? user.avatar : `https://eu.ui-avatars.com/api/?background=0D8ABC&color=fff&name=${user.name}`"
							alt="avatar"
						/>
					</td>
					<td class="px-3 break-all py-2">
					<span class="text-center font-semibold">{{user.name}}</span>
					</td>
					<td class="px-3 break-all py-2 hidden md:table-cell">
					<span>{{user.id}}</span>
					</td>
					<td class="px-3 break-all py-2">
					<span>{{user.email}}</span>
					</td>
					<td class="px-3 break-all py-2">
					<span v-for="(r, i) in getRoles" :key="i" v-if="r.id == user.role_id">{{r.roleName}}</span>
					</td>
					<td class="px-2 break-all py-2 w-5"  v-if="isUpdatePermitted || isDeletePermitted">
						<Button class="w-full" size="small" @click="showEditModal(user, i)" v-if="isUpdatePermitted">
							<Icon type="md-create" />&nbsp;&nbsp;Editar&nbsp;&nbsp;
						</Button>					
						<Button class="w-full" size="small" @click="showDeletingModal(user, i)"  :loading="user.isDeleting" v-if="isDeletePermitted">
							<Icon type="md-trash" /> Borrar
						</Button>
					</td>
				</tr>
				</tbody>
			</table>
		</section>

		<!-- Add modal -->
		<Modal
			v-model="addModal"
			title="Agregar Nuevo"
			:mask-closable="false"
			:closable="false"
			>
			<div class="pt-4">
				<Input type="text" v-model="data.name" placeholder="Nombre Completo"  />
			</div>
			<div class="pt-4">
				<Input type="email" v-model="data.email" placeholder="Correo"  />
			</div>
			<div class="pt-4">
				<Input type="password" v-model="data.password" placeholder="Contraseña"  />
			</div>
			<div class="pt-4">
				<Select v-model="data.role_id"  placeholder="Selecciona el tipo de usuario">
					<Option :value="r.id" v-for="(r, i) in getRoles" :key="i" v-if="getRoles.length">{{r.roleName}}</Option>
				</Select>
			</div>
			<div slot="footer">
				<Button type="default" @click="addModal=false">Cerrar</Button>
				<Button type="primary" @click="addAdmin" :disabled="isAdding" :loading="isAdding">{{isAdding ? 'Registrando..' : 'Agregar Usuario'}}</Button>
			</div>
		</Modal>

		<!-- Edit Modal -->
		<Modal
			v-model="editModal"
			title="Editar Usuario"
			:mask-closable="false"
			:closable="false"
			>
			<div class="pt-4">
				<Input type="text" v-model="editData.name" placeholder="Nombre Completo"  />
			</div>
			<div class="pt-4" v-show="getUser.id == editData.id">
				<Input type="email" v-model="editData.email" placeholder="Correo"  />
			</div>
			<div class="pt-4" v-show="getUser.id == editData.id">
				<Input type="password" v-model="editData.password" placeholder="Contraseña"  />
			</div>
			<div class="pt-4">
				<Select v-model="editData.role_id"  placeholder="Selecciona el tipo de usuario">
					<Option :value="r.id" v-for="(r, i) in getRoles" :key="i" v-if="getRoles.length">{{r.roleName}}</Option>						
				</Select>
			</div>			
			<div slot="footer">
				<Button type="default" @click="editModal=false">Cerrar</Button>
				<Button type="primary" @click="editAdmin" :disabled="isAdding" :loading="isAdding">{{isAdding ? 'Actualizando datos..' : 'Guardar Cambios'}}</Button>
			</div>
		</Modal>
		
		<!-- Delete Modal -->
		<delete-modal></delete-modal>
  	</div>
</template>

<script>
import { mapGetters } from 'vuex';
import DeleteModal from '../../components/DeleteModal.vue';
import RouteBreadcrumb from '../../components/RouteBreadcrumb.vue';
export default {
	data(){
		return {
			data : {
				name: '',
				email: '',
				password: '',
				role_id: null,
			}, 
			addModal : false, 
			editModal : false, 
			isAdding : false,  
			editData : {
				tagName: ''
			}, 
			index : -1, 
			showDeleteModal: false,  
			deletingIndex: -1, 			
		}
    },
    methods : {
		async addAdmin(){
            if(this.data.name.trim()=='') return this.e('El campo nombre es obligatorio');
            if(this.data.email.trim()=='') return this.e('Debe ingresar un correo');
            if(this.data.password.trim()=='') return this.e('Contraseña obligatoria');
            if(!this.data.role_id) return this.e('Seleccione un tipo de usuario');
            
			const res = await this.callApi('post', '/app/create_user', this.data);
			if(res.status===201){
                this.addModal = false;
				this.$store.state.users.unshift(res.data);
				this.s('Usuario agregado correctamente!');
				this.data.tagName = '';
			}else{
				if(res.status==422){
                    for(let i in res.data.errors){
                        this.e(res.data.errors[i][0]);
                    }
				}else{
					this.swr();
				}			
			}
		},
		async editAdmin(){
        	if(this.editData.name.trim()=='') return this.e('El campo nombre es obligatorio');
            if(this.editData.email.trim()=='') return this.e('Debe ingresar un correo');
            if(!this.editData.role_id) return this.e('Contraseña obligatoria');
        	const res = await this.callApi('post', '/app/update_user', this.editData);

			if(res.status===200){
			this.$store.state.users[this.index] = this.editData;
			this.s('Los datos fueron actualizados!');
			this.editModal = false;
			}else{
				if(res.status==422){
					for(let i in res.data.errors){
						this.e(res.data.errors[i][0]);
					}
				}else{
				this.swr();
				}        
        	}
      	},		
      	showEditModal(user, index){
			let obj = {
				id : user.id, 
				name : user.name, 
				email : user.email, 
				role_id : user.role_id, 
			}
			this.editData = obj
			this.editModal = true
			this.index = index
      	},

      	showDeletingModal(user, i){
			this.deletingIndex = i;
			const deleteModalObj  =  {
				showDeleteModal: true, 
				deleteUrl : '/app/delete_user', 
				data : user, 
				deletingIndex: i, 
				isDeleted : false,
				msg : '¿Está seguro que desea eliminar a este usuario?',
				successMsg: 'El usuario ha sido eliminado!'
			}
			this.$store.commit('setDeletingModalObj', deleteModalObj);
		}
	}, 
	 components : {
		DeleteModal,
		RouteBreadcrumb
	},
	async created(){
		if(this.getUsers.length == 0 || this.getRoles.length == 0 ){
			const [res, resRole] = await Promise.all([
				this.callApi('get', '/app/get_users'), 
				this.callApi('get', '/app/get_roles')
			]);
			if(res.status==200){
				this.$store.commit('setUsers', res.data);
			}else{
				if(res.status==403){
					this.e(res.data.msg);
					this.$router.push('/');
					return;
				}else{
					this.swr();
				}			
			}

			if(resRole.status==200){
				this.$store.commit('setRoles', resRole.data);
			}else{
				if(resRole.status==403){
						this.e(resRole.data.msg);
						// this.$router.push('/');				
				}else{
					this.swr();
				}			
			}
		}
	}, 
	computed : {
		...mapGetters(['getDeleteModalObj','getUser','getUsers','getRoles','getUserPermission'])
    },
    watch : {
		getDeleteModalObj(obj){
			if(obj.isDeleted){
				this.$store.state.users.splice(this.deletingIndex,1);
			}
		}
	}   
}
</script>