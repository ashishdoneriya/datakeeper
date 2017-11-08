import Vue from 'vue'
import Router from 'vue-router'
import {
	Input,
	Button,
	Form,
	FormItem,
	Row,
	Col,
	Card,
	Container,
	MessageBox,
	Message,
	Notification
} from 'element-ui'
import './theme/index.css'
const Root = () => import('./Root.vue');
const Login = () => import('./Login.vue');
const Register = () => import('./Register.vue');


Vue.use(Input)
Vue.use(Button)
Vue.use(Form)
Vue.use(FormItem)
Vue.use(Row)
Vue.use(Col)
Vue.use(Card)
Vue.use(Container)
Vue.prototype.$msgbox = MessageBox
Vue.prototype.$alert = MessageBox.alert
Vue.prototype.$confirm = MessageBox.confirm
Vue.prototype.$prompt = MessageBox.prompt
Vue.prototype.$notify = Notification
Vue.prototype.$message = Message

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
		}]
	}),
	render: h => h(Root)
});
