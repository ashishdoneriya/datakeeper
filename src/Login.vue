<template>
	<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
		<el-col :span="6">
			<el-card class="box-card">
				<div slot="header" class="clearfix" style="text-align:center">
					<span>Login</span>
				</div>
				<el-form :label-position="'left'" ref="form" :model="form" label-width="80px">
					<el-form-item label="Email">
						<el-input v-model="form.email"></el-input>
					</el-form-item>
					<el-form-item label="Password">
						<el-input v-model="form.password" type="password"></el-input>
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="login">Login</el-button>
					</el-form-item>
				</el-form>
			</el-card>
			<el-row style="margin-top:30px;text-align:center">
				<router-link to="/register">Register here</router-link>
			</el-row>
		</el-col>
	</el-row>
</template>

<script>
import axios from 'axios';

	export default {
		data() {
			return {
				form: {
					email: '',
					password: ''
				}
			}
		},
		methods: {
			login: function() {
				if (!this.form.email && !this.form.password) {
					this.$notify({
						message: 'Please fill your email id and password',
						type: 'error'
					});
					return;
				} else if (!this.form.email) {
					this.$notify({
						message: 'Please fill your email id',
						type: 'error'
					});
					return;
				} else if (!this.form.password) {
					this.$notify({
						message: 'Please fill your password',
						type: 'error'
					});
					return;
				}
				axios.post('/api/login', {
					'email': this.form.email,
					'password': this.form.password
				}).then(result => {
					if (result == 'success') {
						this.$router.push({
							path: '/admin'
						});
					} else {
						this.$notify({
							title: 'Unable to login',
							type: 'error'
						});
					}
				}).catch(error => {
					this.$notify({
						message: 'Error while checking login details',
						type: 'error'
					});
				});
			}
		}
	}
</script>