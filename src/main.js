import Vue from 'vue'
import Router from 'vue-router'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

import Root from './Root.vue'
import Login from './Login.vue'

Vue.use(Router);

Vue.use(ElementUI)

new Vue({
	el: '#vapp',
	router: new Router({
		mode: 'history',
		routes: [{
			path: '/',
			name: 'login',
			component: Login
		}]
	}),
	render: h => h(Root)
});
