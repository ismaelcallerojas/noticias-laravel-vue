import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        token: '',
        // Usuarios
        users: [],
        user: false, // auth. user 
        userPermission: false,
        // Noticias
        allPosts: [],
        posts: [],  // paginado
        total: 6, // paginación - publicaciones por página
        // Roles, Etiquetas, Categorías
        roles: [],
        categories: [],
        tags: [],
        
        // UI
        sideBarOpen: false,
        // Eliminar datos modales
        deleteModalObj : {
            showDeleteModal: false, 
            deleteUrl : '', 
            data : null, 
            deletingIndex: -1, 
            isDeleted : false,
        },
        showDeleteModal: false,
        // Avatar
        avatarModal: false
    },
    getters: {
        // Usuarios
        getUser(state){
            return state.user
        },
        getUsers(state){
            return state.users
        },
        getUserPermission(state){
            return state.userPermission
        },
        // Noticias
        getPosts(state){
            return state.posts
        },
        getAllPosts(state){
            return state.allPosts
        },
        // Roles
        getRoles(state){
            return state.roles
        },
        // Categorias
        getCategories(state){
            return state.categories
        },
        // Etiquetas
        getTags(state){
            return state.tags
        },
        // UI
        sideBarOpen: state => {
            return state.sideBarOpen
        },
        getDeleteModalObj(state){
            return state.deleteModalObj
        },        
    },
    // Acciones - mutaciones de llamadas
    actions: {
        // Usuarios
        setUser({commit},data) {
            commit('setUser',data);
        },
        setUsers({commit},data) {
            commit('setUsers',data);
        },
        setUserPermission({commit},data) {
            commit('setUserPermission',data);
        },
        // UI
        toggleSidebar(context) {
            context.commit('toggleSidebar');
        },
    },
    // Mutaciones - cambio directo de estado
    mutations: {
        // Usuarios
        setUser(state, data){
            state.user = data
        },
        setUsers(state, data){
            state.users = data
        },
        setUserPermission(state, data){
            state.userPermission = data
        },
        setUserAvatar(state, data){
            state.user.avatar = data
        },
        // Noticias
        setPosts(state, data){
            state.posts = data
        },
        setAllPosts(state, data){
            state.allPosts = data
        },
        // Roles
        setRoles(state, data){
            state.roles = data
        },
        // Categorias
        setCategories(state, data){
            state.categories = data
        },
        // Etiquetas
        setTags(state, data){
            state.tags = data
        },
        // UI
        toggleSidebar (state) {
            state.sideBarOpen = !state.sideBarOpen
        },
        setDeleteModal(state, data){
            const deleteModalObj = {
                showDeleteModal: false, 
                deleteUrl : '', 
                data : null, 
                deletingIndex: -1, 
                isDeleted : data,
            }
            state.deleteModalObj = deleteModalObj
        },
        setDeletingModalObj(state, data){
            state.deleteModalObj = data
        },
    },

    modules: {
        
    }
})
