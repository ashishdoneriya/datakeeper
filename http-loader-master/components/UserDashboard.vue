<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center" style="margin-top:100px">
			<el-col :span="8">
				<el-card class="box-card">
					<div slot="header" class="clearfix">
						<span>Tables</span>
						<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-circle-plus" @click="addTable()">Add</el-button>
					</div>
					<div v-for="table in tablesList" :key="table" class="text item">
						<a @click="openTable(table.tableName)"> {{table.displayedTableName }}</a>
						<el-button @click="removeTable(table)" style="float: right; padding: 3px 5px;" type="text" icon="el-icon-delete">Remove</el-button>
						<el-button @click="modifyTable(table)" style="float: right; padding: 3px 5px;margin-right:10px;" type="text" icon="el-icon-edit">Modify</el-button>
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
		methods: {
			openTable(tableName) {
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
					axios.post('/api/remove-table.php', {
						tableName: table.tableName
					}).then((result => {
						this.$message({
							type: 'success',
							message: `Table '${table.displayedTableName}' has been removed`
						});
						this.$store.commit('update');
					})).catch(error => {

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