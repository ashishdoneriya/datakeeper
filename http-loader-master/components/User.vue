<template>
	<el-container>
		<el-header>
			<el-menu :default-active="'1'" class="el-menu-demo" mode="horizontal" background-color="#545c64" text-color="#fff" active-text-color="#ffd04b">
				<el-menu-item index="1">Home</el-menu-item>
				<el-menu-item index="2">
					<el-select v-model="value" placeholder="Select Table">
						<el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
						</el-option>
					</el-select>
				</el-menu-item>
				<el-submenu index="3" style="float:right;min-width:75px;text-align:right">
					<template slot="title">Hi {{user.name}}</template>
					<el-menu-item index="3-1" @click="logout()" style="max-width:75px;min-width:75px;">Logout</el-menu-item>
				</el-submenu>
			</el-menu>
		</el-header>
		<el-main><router-view></router-view></el-main>
	</el-container>
</template>

<script>
module.exports = {
	data() {
		return {
			user : {},
			options: [
				{
					value: "Option1",
					label: "Option1"
				},
				{
					value: "Option2",
					label: "Option2"
				},
				{
					value: "Option3",
					label: "Option3"
				},
				{
					value: "Option4",
					label: "Option4"
				},
				{
					value: "Option5",
					label: "Option5"
				}
			],
			value: ""
		};
	},
	created: function() {
		this.getUserInfo();
	},
	methods: {
		getUserInfo() {
			axios.get('/api/user/info.php')
				.then(result => {
					console.log(result);
					this.user = result.data;

				}).catch(error => {
					this.$notify({
						message: 'Unable to fetch your details',
						type: 'error'
					});
				});
		},
		logout() {
			axios.post("/api/logout.php");
			this.$router.push({
				path: "/"
			});
		}
	}
};
</script>