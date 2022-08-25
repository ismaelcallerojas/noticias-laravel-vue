<template>
  <div>
    <!-- breadcrumb -->
		<route-breadcrumb></route-breadcrumb>

    <div v-if="isWritePermitted">
      <div class="lg:flex justify-between items-center mb-6">
        <p class="text-2xl font-semibold mb-2 lg:mb-0">Gestión de permisos</p>
        <div>
          <span class="font-semibold">Seleccionar Rol </span>
          <Select v-model="data.id"  placeholder="Select admin type" style="width:300px" @on-change="changeAdmin">
            <Option :value="r.id" v-for="(r, i) in getRoles" :key="i" v-if="getRoles.length">{{r.roleName}}</Option>
          </Select>
        </div>
      </div>
      <!-- Tabla - Permisos -->
      <section class="text-gray-700 w-full bg-white border rounded-lg p-4">
        <table class="min-w-full table-auto border rounded-lg">
          <thead class="text-left">
            <tr class="bg-gray-700">
              <th class="px-2 sm:px-3 py-2"><span class="text-gray-300">Módulo</span></th>
              <th class="px-2 sm:px-3 py-2"><span class="text-gray-300">Leer</span></th>
              <th class="px-2 sm:px-3 py-2"><span class="text-gray-300">Escribir</span></th>
              <th class="px-2 sm:px-3 py-2"><span class="text-gray-300">Actualizar</span></th>
              <th class="px-2 sm:px-3 py-2"><span class="text-gray-300">Borrar</span></th>				
            </tr>
          </thead>
          <tbody class="bg-gray-200">
            <tr class="bg-white border-4 border-gray-200"  v-for="(r, i) in resources" :key="i">
              <td class="px-2 sm:px-3 break-all py-2">
              <span class="text-center font-semibold">{{r.resourceName}}</span>
              </td>
              <td class="px-2 sm:px-3 break-all py-2">
                  <i-switch true-color="#4A5568" v-model="r.read" >
                      <Icon type="md-checkmark" slot="open"></Icon>
                      <Icon type="md-close" slot="close"></Icon>
                  </i-switch>
              </td>
              <td class="px-2 sm:px-3 break-all py-2">
                  <i-switch true-color="#4A5568" v-model="r.write" >
                      <Icon type="md-checkmark" slot="open"></Icon>
                      <Icon type="md-close" slot="close"></Icon>
                  </i-switch>
              </td>
              <td class="px-2 sm:px-3 break-all py-2">
                  <i-switch true-color="#4A5568" v-model="r.update" >
                      <Icon type="md-checkmark" slot="open"></Icon>
                      <Icon type="md-close" slot="close"></Icon>
                  </i-switch>
              </td>
              <td class="px-2 sm:px-3 break-all py-2">
                  <i-switch true-color="#4A5568" v-model="r.delete" >
                      <Icon type="md-checkmark" slot="open"></Icon>
                      <Icon type="md-close" slot="close"></Icon>
                  </i-switch>
              </td>        
            </tr>
          </tbody>
        </table>
        <div class="py-4">
          <Button size="large" :loading="isSending" :disabled="isSending" @click="assignRoles"><Icon type="md-create" /> Asignar Permisos</Button>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import RouteBreadcrumb from '../../components/RouteBreadcrumb.vue';
export default {
  data() {
    return {
      data: {
        id: null,
      },
      isSending : false, 
      roles: [],

      // Resourcess describe routeName,path and all CRUD operations
      // Resource name must match names of the routes in our vue router (which containes data that may have permission limits set up)
      resources: [
            {resourceName: 'Inicio', read: false, write: false, update: false, delete: false, name: '/app'},
            {resourceName: 'Usuarios', read: false, write: false, update: false, delete: false, name: '/app/users'},
            {resourceName: 'Roles', read: false, write: false, update: false, delete: false, name: '/app/roles'},
            {resourceName: 'Permisos', read: false, write: false, update: false, delete: false, name: '/app/permissions'},
            {resourceName: 'Noticias', read: false, write: false, update: false, delete: false, name: '/app/posts'},
            {resourceName: 'Escribir Noticia', read: false, write: false, update: false, delete: false, name: '/app/createpost'},
            {resourceName: 'Categorias', read: false, write: false, update: false, delete: false, name: '/app/categories'},
            {resourceName: 'Etiquetas', read: false, write: false, update: false, delete: false, name: '/app/tags'},
            
        ],
        // Default permission set (used when role has no permissions assigned)
      defaultResourcesPermission: [
            {resourceName: 'Inicio', read: false, write: false, update: false, delete: false, name: '/app'},
            {resourceName: 'Usuarios', read: false, write: false, update: false, delete: false, name: '/app/users'},
            {resourceName: 'Roles', read: false, write: false, update: false, delete: false, name: '/app/roles'},
            {resourceName: 'Permisos', read: false, write: false, update: false, delete: false, name: '/app/permissions'},
            {resourceName: 'Noticias', read: false, write: false, update: false, delete: false, name: '/app/posts'},
            {resourceName: 'Escribir Noticia', read: false, write: false, update: false, delete: false, name: '/app/createpost'},
            {resourceName: 'Categorias', read: false, write: false, update: false, delete: false, name: '/app/categories'},
            {resourceName: 'Etiquetas', read: false, write: false, update: false, delete: false, name: '/app/tags'},
        ],
    };
  },
  methods: {
    async assignRoles(){
      let data = JSON.stringify(this.resources);
      const res = await this.callApi('post','/app/assign_roles', {'permission' : data, id: this.data.id});
      if(res.status==200){
        this.s('Asignado Correctamente!');
        let index = this.roles.findIndex(role => role.id == this.data.id);
        this.roles[index].permission = data;
      }else{
        this.swr();
      }
    }, 
    changeAdmin(){ // Change currently displayed role
      let index = this.roles.findIndex(role => role.id == this.data.id);
      let permission = this.roles[index].permission;
      if(!permission){
        // If role has no permissions assigned show default permission table (all set to false) 
          this.resources = this.defaultResourcesPermission
      }else{
        this.resources = JSON.parse(permission)
      }    
    }
  },
  
  async created() {
    const res = await this.callApi('get', '/app/get_roles');
    if (res.status == 200) {
      this.roles = res.data;
      this.$store.commit('setRoles',res.data);
      if(res.data.length){
        this.data.id = res.data[0].id;
        if(res.data[0].permission){
          // Set resources as res.data.permissions
          this.resources = JSON.parse(res.data[0].permission);        
        }
      }
    } else {
      this.swr();
    }
  },
  computed : {
  ...mapGetters(['getRoles'])
  },
  components : {
		RouteBreadcrumb
	}
};
</script> 