<template>
	<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
		<el-col :xs="24" :sm="16" :md="12" :lg="8">
			<el-card class="box-card">
				<div slot="header" class="clearfix" style="text-align:center">
					<span>Registration Form</span>
				</div>
				<el-form :label-position="'left'" ref="form"  label-width="140px">
					<el-form-item label="Name">
						<el-input v-model="name"></el-input>
					</el-form-item>
					<el-form-item label="Email">
						<el-input v-model="email"></el-input>
					</el-form-item>
					<el-form-item label="Password">
						<el-input v-model="password" type="password"></el-input>
					</el-form-item>
					<el-form-item label="Confirm Password">
						<el-input v-model="password1" type="password"></el-input>
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="register">Register</el-button>
					</el-form-item>
				</el-form>
			</el-card>
			<el-row style="margin-top:30px;text-align:center">
				<router-link to="/signin">Go Back</router-link>
			</el-row>
		</el-col>
	</el-row>
</template>

<script>

	module.exports = {
		data() {
			return {
				name: '',
				email: '',
				password: '',
				password1: ''
			}
		},
		methods: {
			register: function() {
				if (this.isEmpty(this.name) ||
					this.isEmpty(this.email) ||
					this.isEmpty(this.password) ||
					this.isEmpty(this.password1)) {
					this.showError('Please fill all the fields');
					return;
				}
				if (this.password != this.password1) {
					this.showError('Password mismatch');
					return;
				}
				axios.post('/api/signup.php', {
					'name': this.name,
					'email': this.email,
					'password': this.password
				}).then(response => {
					if (response.data.status == 'success') {
						this.$message({
							message: response.data.message,
							type: 'success'
						});
						setTimeout(() => {
							this.$router.push('/signin');
						}, 2000);
					} else {
						this.$message({
							message: response.data.message,
							type: 'error'
						});
					}
				}).catch(error => {
					this.showError('Unable to register');
				});
			},
			showError(msg) {
				this.$message({
					message: msg,
					type: 'error'
				});
			},
			isEmpty: function(field) {
				return !field ? true : false;
			}
		}
	}
</script>