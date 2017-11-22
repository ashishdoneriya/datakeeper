<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
			<el-col :span="16">
				<el-card class="box-card" style="width:100%">
					<div slot="header" class="clearfix">
						<span>Tables</span>
						<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-circle-plus" @click="addTable()">Add</el-button>
					</div>
					<div v-for="table in tablesList.personalTables.list" :key="table" class="text item">
						<a @click="openTable(table.tableName)"> {{table.displayedTableName }}</a>
						<el-button @click="removeTable(table)" style="float: right; padding: 3px 5px;" type="text" icon="el-icon-delete">Remove</el-button>
						<el-button @click="modifyTable(table)" style="float: right; padding: 3px 5px;margin-right:10px;" type="text" icon="el-icon-edit">Modify</el-button>
						<el-button @click="modifyTable(table)" style="float: right; padding: 3px 5px;margin-right:10px;" type="text"> Change Permissions</el-button>
					</div>
				</el-card>
			</el-col>
		</el-row>
	</el-main>
</template>

<script>
	module.exports = {
		computed: {
			tablesList() {
				return this.$store.state.list;
			}
		},
		created() {
			this.$store.commit('setCurrentTable', '');
		},
		methods: {
			openTable(tableName) {
				this.$store.commit('setCurrentTable', tableName);
				this.$router.push(`/user/table/${tableName}`);
			},
			addTable() {
				this.$router.push('/user/form-builder');
			},
			modifyTable(table) {
				this.$router.push(`/user/form-builder/${table.tableName}`);
			},
			removeTable(table) {
				this.$confirm(`Are you sure want to remove the table '${table.displayedTableName}' ?`, 'Warning', {
					confirmButtonText: 'OK',
					cancelButtonText: 'Cancel',
					type: 'warning'
				}).then(() => {
					axios.post('/api/table-delete.php', {
						tableName: table.tableName
					}).then((result => {
						if (result.data.status == 'success') {
							this.$message({
								type: 'success',
								message: `Table '${table.displayedTableName}' has been removed`
							});
							this.$store.commit('update');
						} else {
							this.$message({
								type: 'error',
								message: result.data.message
							});
						}

					})).catch(error => {
						this.$message({
							type: 'info',
							message: `Unable to remove table '${table.displayedTableName}'`
						});
					});

				}).catch(() => {
					this.$message({
						type: 'info',
						message: `Unable to remove table '${table.displayedTableName}'`
					});
				});
			}
		}
	}
</script>


<style>
	.text {
		font-size: 14px;
	}

	.item {
		margin-bottom: 18px;
	}

	.clearfix:before,
	.clearfix:after {
		display: table;
		content: "";
	}

	.clearfix:after {
		clear: both;
	}

	.box-card {
		width: 480px;
	}
</style>