<template>
<div class="min-h-screen flex flex-col">
    <navbar></navbar>
    <!-- Modal del Avatar  -->
    <avatar-modal v-if="$store.state.avatarModal"></avatar-modal>

 <!-- Texto del Header -->
    <blog-header :subtitle="'Últimas Noticias'"></blog-header>

    <!-- Categorias Nav -->
    <categories-nav></categories-nav>

    <!-- contenedor principal -->
    <div class="container mx-auto flex flex-wrap py-6">
        <!--  Seccion Noticias -->
        <section class="w-full md:w-2/3 px-3">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
				<post-card v-for="post in getPosts" :key="post.id" :postData="post" v-if="getPosts"></post-card>
			</div>		

            <!-- Paginacion -->
            <div class="py-8 w-full">
				<Page 
				class-name="text-center"
				:total="pageInfo.total"  
				:current="pageInfo.current_page" 
				:page-size="parseInt(pageInfo.per_page)"
				@on-change="getPostData" 
				v-if="pageInfo" />
            </div>

        </section>

        <!-- Sidebar Seccion -->
        <blog-sidebar></blog-sidebar>
    </div>
	<Footer></Footer>    
</div>
</template>

<script>
import { mapGetters } from 'vuex';
import Navbar from '../../components/Navbar.vue';
import AvatarModal from '../../components/AvatarModal.vue';
import Footer from '../../components/Footer.vue';
import PostCard from '../../components/PostCard.vue';
import CategoriesNav from '../../components/CategoriesNav.vue';
import BlogSidebar from '../../components/BlogSidebar.vue';
import BlogHeader from '../../components/BlogHeader.vue';

export default {
    data(){
       return {
			isLoggedIn : false,
		  	total: this.$store.state.total, // paginacion - noticias por pagina
			pageInfo: {}
       }
	},
	methods: {
		async getPostData(page=1) {
			const res = await this.callApi('get', `/getPosts?page=${page}&total=${this.total}`);
			if(res.status==200){
				this.$store.commit('setPosts',res.data.data); // obtener datos paginados
				this.pageInfo = res.data; // obtener el número de página/enlaces a anterior/siguiente			
			}else{
				this.swr();
			}
		}
	},
    components: {
        Navbar,
		AvatarModal,
		Footer,
        PostCard,
        CategoriesNav,
        BlogSidebar,
        BlogHeader
	},
	computed : {
		...mapGetters(['getPosts'])
	},
	async created () {
        this.getPostData();
    }
}
</script>