<template>
	<el-container>
		<el-header>
			<el-menu :default-active="'1'" class="el-menu-demo" mode="horizontal" background-color="#545c64" text-color="#fff" active-text-color="#ffd04b">
				<el-menu-item index="1">
					<router-link to="/user/dashboard">Home</router-link>
				</el-menu-item>
				<el-menu-item index="2">
					<el-select v-model="selectedTable" @change="openTable(this.selectedTable)" placeholder="Select Table">
						<el-option v-for="table in tablesList" :key="table" :label="table.displayedTableName" :value="table.tableName">
						</el-option>
					</el-select>
				</el-menu-item>
				<el-submenu index="3" style="float:right;min-width:75px;text-align:right">
					<template slot="title">Hi {{user.name}}</template>
					<el-menu-item index="3-1" @click="logout()" style="max-width:75px;min-width:75px;">Logout</el-menu-item>
				</el-submenu>
			</el-menu>
		</el-header>
		<router-view></router-view>
	</el-container>
</template>

<script>
	module.exports = {
		data() {
			return {
				user: {},
				selectedTable : ''
			};
		},
		computed: {
			tablesList() {
				return this.$store.state.list;
			}
		},
		created: function() {
			this.getUserInfo();
			this.$store.commit('update');
			var tableName = this.$route.params.tableName;
			if (tableName) {
				this.selectedTable = tableName;
			}
		},
		methods: {
			openTable(tableName) {
				this.$router.push('/user/table/' + this.selectedTable);
			},
			getUserInfo() {
				axios.get('/api/user/info.php')
					.then(result => {
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