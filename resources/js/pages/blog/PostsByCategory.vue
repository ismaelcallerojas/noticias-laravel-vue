<template>
<div class="min-h-screen flex flex-col">
    <navbar></navbar>
    <!-- Avatar Modal -->
    <avatar-modal v-if="$store.state.avatarModal"></avatar-modal>

    <!-- Texto del Header  -->
    <blog-header :subtitle="`Categoria : ${categoryName}`"></blog-header>

    <!-- Categorias Nav -->
    <categories-nav></categories-nav>

    <!-- contenedor principalr -->
    <div class="container mx-auto flex flex-wrap py-6">
        <!-- Seccion Noticias  -->
        <section class="w-full md:w-2/3 px-3">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
				<post-card v-for="post in sortedByCategory" :key="post.id" :postData="post"></post-card>
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

        <!-- Sidebar Section -->
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
            total: this.$store.state.total,
            pageInfo: {},
            sortedByCategory: [],
            categoryName: ''
        }
	},
	methods: {
		async getPostData(page=1) {
			const res = await this.callApi('get', `/categories/${this.$route.params.categoryName}/${this.$route.params.id}?page=${page}&total=${this.total}`);
			if(res.status==200){
				this.sortedByCategory = res.data.posts.data; // obtener datos paginados
				this.categoryName = res.data.categoryName; // obtener datos paginados de categorias
				this.pageInfo = res.data.posts; // obtener el n??mero de p??gina/enlaces a anterior/siguiente			
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
	async created () {
        this.getPostData();
    }
}
</script>