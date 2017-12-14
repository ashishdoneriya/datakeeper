<template>
	<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
		<el-col :xs="24" :sm="16" :md="12" :lg="8">
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
						<el-checkbox v-model="stayLoggedIn">Stay Logged in</el-checkbox>
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="login">Login</el-button>
					</el-form-item>
				</el-form>
			</el-card>
			<el-row style="margin-top:30px;text-align:center">
				<router-link to="/signup">Register here</router-link>
			</el-row>
		</el-col>
	</el-row>
</template>

<script>

	module.exports = {
		data() {
			return {
				form: {
					email: '',
					password: '',
					stayLoggedIn : false
				}
			}
		},
		methods: {
			login: function() {
				if (!this.form.email && !this.form.password) {
					this.$message({
						message: 'Please fill your email id and password',
						type: 'error'
					});
					return;
				} else if (!this.form.email) {
					this.$message({
						message: 'Please fill your email id',
						type: 'error'
					});
					return;
				} else if (!this.form.password) {
					this.$message({
						message: 'Please fill your password',
						type: 'error'
					});
					return;
				}
				axios.post('/api/signin.php', {
					'email': this.form.email,
					'password': this.form.password
				}).then(result => {
					console.log(result);
					if (result.data.status == 'success') {
						if (this,stayLoggedIn) {
							Cookies.set('email', result.data.email, { expires: Infinity });
						} else {
							Cookies.set('email', result.data.email, { expires: 86400 }); // 1 day
						}
						this.$router.push({
							path: '/user/dashboard'
						});
					} else {
						this.$message({
							message: result.data.message,
							type: 'error'
						});
					}
				}).catch(error => {
					this.$message({
						message: 'Error while checking login details',
						type: 'error'
					});
				});
			}
		}
	}
</script>