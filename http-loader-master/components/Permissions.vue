<template>
	<el-main>
		<el-row type="flex" class="row-bg" justify="center">
			<el-col class="text-center">
				<h2><u>{{displayedTableName}}</u> Permissions</h2>
			</el-col>
		</el-row>
		<el-row>
			<h3>Global Permissions</h3>
			<el-form label-position="top">
				<el-form-item label="Allow all peoples to view records of this Table">
					<el-radio-group v-model="publicRoles.read.allowed">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="Allow all peoples to ADD record to this table">
					<el-radio-group v-model="publicRoles.add.allowed" style="margin-right:30px">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.add.approval" :disabled="!publicRoles.add.allowed">You have to <u>approve</u> first, after that the record will be added</el-checkbox>
					<el-checkbox v-model="publicRoles.add.loginRequired" :disabled="!publicRoles.add.allowed"><u>Login required</u> for a person to add record</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to MODIFY records of this table">
					<el-radio-group v-model="publicRoles.update.allowed" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.update.approval" :disabled="!publicRoles.update.allowed">You have to <u>approve</u> first, after that the record will be modified</el-checkbox>
					<el-checkbox v-model="publicRoles.update.loginRequired" :disabled="!publicRoles.update.allowed"><u>Login required</u> for a person to modify records</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to REMOVE records from this table">
					<el-radio-group v-model="publicRoles.delete.allowed" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.delete.approval" :disabled="!publicRoles.delete.allowed">You have to <u>approve</u> first, after that the record will be deleted</el-checkbox>
					<el-checkbox v-model="publicRoles.delete.loginRequired" :disabled="!publicRoles.delete.allowed"><u>Login required</u> for a person to delete records</el-checkbox>
				</el-form-item>
			</el-form>
		</el-row>
		<el-row>
			<h3>Individual User Permissions</h3>
			<el-form label-position="top">
				<el-form-item label="Allow all peoples to view records of this Table">
					<el-radio-group v-model="publicRoles.read.allowed">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item label="Allow all peoples to ADD record to this table">
					<el-radio-group v-model="publicRoles.add.allowed" style="margin-right:30px">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.add.approval" :disabled="!publicRoles.add.allowed">You have to <u>approve</u> first, after that the record will be added</el-checkbox>
					<el-checkbox v-model="publicRoles.add.loginRequired" :disabled="!publicRoles.add.allowed"><u>Login required</u> for a person to add record</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to MODIFY records of this table">
					<el-radio-group v-model="publicRoles.update.allowed" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.update.approval" :disabled="!publicRoles.update.allowed">You have to <u>approve</u> first, after that the record will be modified</el-checkbox>
					<el-checkbox v-model="publicRoles.update.loginRequired" :disabled="!publicRoles.update.allowed"><u>Login required</u> for a person to modify records</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to REMOVE records from this table">
					<el-radio-group v-model="publicRoles.delete.allowed" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.delete.approval" :disabled="!publicRoles.delete.allowed">You have to <u>approve</u> first, after that the record will be deleted</el-checkbox>
					<el-checkbox v-model="publicRoles.delete.loginRequired" :disabled="!publicRoles.delete.allowed"><u>Login required</u> for a person to delete records</el-checkbox>
				</el-form-item>
			</el-form>
		</el-row>
	</el-main>
</template>

<script>
	module.exports = {
		data : function() {
			return {
				tableName : '',
				displayedTableName : '',
				publicRoles : {
					"read" : {"allow" : false},
					"add" : {"allow" : false, "approval" : true, "loginRequired" : true},
					"update" : {"allow" : false, "approval" : true, "loginRequired" : true},
					"delete" : {"allow" : false, "approval" : true, "loginRequired" : true}
				},
				fields : [],
				guestPermissions : [],
				admins : []
			};
		},
		created() {
			this.fetchPermissions();
		},
		methods : {
			fetchPermissions() {
				this.tableName = this.$route.params.tableName;
				axios.get(`/api/table-permissions.php?tableName=${this.tableName}`)
					.then(result => {
						this.displayedTableName = result.data.displayedTableName;
						this.fields = result.data.fields;
						this.publicRoles = result.data.publicRoles;
						console.log(this.publicRoles);
						this.guestPermissions = result.data.guestPermissions;
						this.admins = result.data.admins;
					}).catch(error => {
						this.$message({
							message: 'Unable to fetch table information',
							type: 'error'
						});
					});
			}
		}
	}
</script>
