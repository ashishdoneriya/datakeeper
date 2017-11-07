import Vue from 'vue'
import Router from 'vue-router'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import './theme/index.css'
import Root from './Root.vue'
import Login from './Login.vue'
import Register from './Register.vue'

Vue.use(Router);

Vue.use(ElementUI)

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
		}]
	}),
	render: h => h(Root)
});
