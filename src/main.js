import Vue from 'vue'
import Router from 'vue-router'
import ElementUI  from 'element-ui'
import './theme/index.css'
const Admin = () => import('./Admin.vue');
const AdminHome = () => import('./AdminHome.vue');
const FormBuilder = () => import('./FormBuilder.vue');
const Login = () => import('./Login.vue');
const Register = () => import('./Register.vue');
const Root = () => import('./Root.vue');


Vue.use(ElementUI);

Vue.use(Router);

new Vue({
	el: '#vapp',
	router: new Router({
		mode: 'history',
		routes: [{
			path: '/',
			component: Login,
			alias : '/login'
		}, {
			path: '/register',
			component: Register
		}, {
			path: '/admin',
			component: AdminHome
		}]
	}),
	render: h => h(Root)
});
