<template>
	<el-container>
		<el-header>
			<el-menu :default-active="'1'" class="el-menu-demo" mode="horizontal" background-color="#002B36" text-color="#fff" active-text-color="#ffd04b">
				<el-menu-item @click="goToHome()" index="1">
					<a>Home</a>
				</el-menu-item>
				<el-menu-item index="2" v-if="tablesList.personalTables.list.length > 0 || tablesList.otherTables.list.length > 0">
					<el-select v-if="tablesList.personalTables.list.length > 0 && tablesList.otherTables.list.length == 0" v-model="selectedTable" @change="openTable(this.selectedTable)" placeholder="Select Table">
						<el-option key="" label="Select Table" value="" disabled="true"></el-option>
						<el-option
							v-for="table in tablesList.personalTables.list"
							:key="table"
							:label="table.displayedTableName"
							:value="table.tableName"
						></el-option>
					</el-select>
					<el-select v-if="tablesList.personalTables.list.length == 0 && tablesList.otherTables.list.length > 0" v-model="selectedTable" @change="openTable(this.selectedTable)" placeholder="Select Table">
						<el-option key="" label="Select Table" value="" disabled="true"></el-option>
						<el-option
							v-for="table in tablesList.otherTables.list"
							:key="table"
							:label="table.displayedTableName"
							:value="table.tableName"
						></el-option>
					</el-select>
					<el-select v-if="tablesList.personalTables.list.length > 0 && tablesList.otherTables.list.length > 0" v-model="selectedTable" @change="openTable(this.selectedTable)" placeholder="Select Table">
						<el-option-group
							v-for="(value, key) in tablesList" :key="key" :label="value.label" v-if="value.list.length > 0">
							<el-option v-for="table in value.list" :key="table" :label="table.displayedTableName" :value="table.tableName">
							</el-option>
						</el-option-group>
					</el-select>
				</el-menu-item>
				<el-submenu index="3" style="float:right;min-width:75px;text-align:right">
					<template slot="title"><span>Hi {{user.name}}</span></template>
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
				selectedTable: ""
			};
		},
		computed: {
			storeCurrentTable() {
				return this.$store.state.currentTable;
			},
			tablesList() {
				return this.$store.state.list;
			}
		},
		watch: {
			storeCurrentTable(newTable, oldTable) {
				this.selectedTable = newTable;
			}
		},
		created: function() {
			this.getUserInfo();
			this.$store.commit("update");
			var tableName = this.$route.params.tableName;
			if (tableName) {
				this.selectedTable = tableName;
			}
		},
		methods: {
			goToHome() {
				this.selectedTable = "";
				this.$router.push("/user/dashboard");
			},
			openTable(tableName) {
				if (this.selectedTable == "") {
					return;
				}
				this.$router.push("/user/table/" + this.selectedTable);
			},
			getUserInfo() {
				axios
					.get("/api/user/info.php")
					.then(result => {
						this.user = result.data;
					})
					.catch(error => {
						this.$notify({
							message: "Unable to fetch your details",
							type: "error"
						});
					});
			},
			logout() {
				axios.post("/api/signout.php");
				this.$router.push({
					path: "/"
				});
			}
		}
	};
</script>