/* Este archivo se usa como mixin en app.js y contiene métodos compartidos por los componentes de vue.
    Los métodos deben llamarse de la misma manera que los métodos de componentes internos (this.methodName)*/
import { mapGetters } from 'vuex';
export default {
    methods: {
        // Método principal para enviar la solicitud al servidor
        async callApi(method, url, dataObj ){
            try {
              return await axios({
                    method: method,
                    url: url,
                    data: dataObj
                });
            } catch (e) {
                return e.response;
            }
        },
 
        // Métodos ViewUI utilizados para mostrar notificaciones,
        i(desc, title="Hey") {
            this.$Notice.info({
                title: title,
                desc: desc
            });
        },
        s(desc, title="Excelente!") {
            this.$Notice.success({
                title: title,
                desc: desc
            });
        },
        w(desc, title="Advertencia!") {
            this.$Notice.warning({
                title: title,
                desc: desc
            });
        },
        e(desc, title="Error!") {
            this.$Notice.error({
                title: title,
                desc: desc
            });
        }, 
        swr(desc='¡Algo salió mal! Inténtalo de nuevo.', title="Oops") {
            this.$Notice.error({
                title: title,
                desc: desc
            });
        },
        // File upload notifications 
		handleError (res, file) {
			this.$Notice.warning({
				title: 'El formato del archivo es incorrecto.',
				desc: `${file.errors.file.length ? file.errors.file[0] : 'Algo salió mal!'}`
			});
		},
		handleFormatError (file) {
			this.$Notice.warning({
				title: 'El formato del archivo es incorrecto.',
				desc: 'Formato de archivo ' + file.name + ' es incorrecto, seleccione jpg o png.'
			});
		},
		handleMaxSize (file) {
			this.$Notice.warning({
				title: 'Excediendo el límite de tamaño de archivo',
				desc: 'El archivo  ' + file.name + ' es demasiado grande, seleccione un archivo menor a 1MB.'
			});
		},

        // Compruebe si el usuario tiene permiso para crear/leer/actualizar/eliminar  
        checkUserPermission(key){
            // Si no hay ningún objeto de permisos, permita que todo / de lo contrario encuentre el nombre del permiso que coincida con el nombre de la ruta actual, si la clave (c, r, u, d) es verdadera, rompa y devuelva verdadero
            if(!this.userPermission) return true;
            let isPermitted = false;
            for(let d of this.userPermission){
                if(this.$route.path==d.name){
                    if(d[key]){
                        isPermitted = true;
                        break;
                    }else{
                        break;
                    }
                }    
            }
            return isPermitted;
        }
    },

    computed: {
        ...mapGetters({
            'userPermission' : 'getUserPermission'
        }),
        isReadPermitted(){
           return this.checkUserPermission('read');
        },
        isWritePermitted(){
            return this.checkUserPermission('write');
        },
        isUpdatePermitted(){
            return this.checkUserPermission('update');
        },
        isDeletePermitted(){
            return this.checkUserPermission('delete');
        },
    }, 
}