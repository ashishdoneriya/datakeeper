<template>
	<el-main>
		<el-button @click="goBack()" icon="el-icon-back" type="primary" round>Go Back</el-button>
		<el-row type="flex" class="row-bg" justify="center">
			<el-col class="text-center">
				<h2><u>{{displayedTableName}}</u> Permissions</h2>
			</el-col>
		</el-row>
		<el-row>
			<h3>Public</h3>
			<el-form label-position="top">
				<el-form-item label="Allow all peoples to view records of this Table">
					<el-radio-group v-model="publicRoles.read.allowed" @change="updateGlobalRoles()">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
				</el-form-item>
				<el-form-item v-show="publicRoles.read.allowed" label="Select fields which will be displayed to the public">
					<el-select v-model="allowedFields" multiple collapse-tags  @change="updateGlobalFields()" placeholder="Select">
						<el-option v-for="field in fields" :key="field.id" :label="field.name" :value="field.id">
						</el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="Allow all peoples to ADD record to this table">
					<el-radio-group v-model="publicRoles.add.allowed" @change="updateGlobalRoles()" style="margin-right:30px">
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.add.approval" :disabled="!publicRoles.add.allowed" @change="updateGlobalRoles()">You have to <u>approve</u> first, after that the record will be added</el-checkbox>
					<el-checkbox v-model="publicRoles.add.loginRequired" :disabled="!publicRoles.add.allowed" @change="updateGlobalRoles()"><u>Login required</u> for a person to add record</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to MODIFY records of this table">
					<el-radio-group v-model="publicRoles.update.allowed" @change="updateGlobalRoles()" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.update.approval" :disabled="!publicRoles.update.allowed" @change="updateGlobalRoles()">You have to <u>approve</u> first, after that the record will be modified</el-checkbox>
					<el-checkbox v-model="publicRoles.update.loginRequired" :disabled="!publicRoles.update.allowed" @change="updateGlobalRoles()"><u>Login required</u> for a person to modify records</el-checkbox>
				</el-form-item>
				<el-form-item label="Allow all peoples to REMOVE records from this table">
					<el-radio-group v-model="publicRoles.delete.allowed" @change="updateGlobalRoles()" style="margin-right:30px">>
						<el-radio :label="true">Yes</el-radio>
						<el-radio :label="false">No</el-radio>
					</el-radio-group>
					<el-checkbox v-model="publicRoles.delete.approval" :disabled="!publicRoles.delete.allowed" @change="updateGlobalRoles()">You have to <u>approve</u> first, after that the record will be deleted</el-checkbox>
					<el-checkbox v-model="publicRoles.delete.loginRequired" :disabled="!publicRoles.delete.allowed" @change="updateGlobalRoles()"><u>Login required</u> for a person to delete records</el-checkbox>
				</el-form-item>
			</el-form>
		</el-row>
		<el-row>
			<h3>Guests
				<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-circle-plus" @click="guestAddDialog = true">Add Guest</el-button>
			</h3>
			<el-card class="box-card" v-for="(guest, index) in guestPermissions" :key="guest.userId">
				<div slot="header" class="clearfix" style="text-align:center">
					<span>Name : {{guest.name}} | Email : {{guest.email}}</span>
					<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-delete" @click="removeGuest(guest, index)">Remove Guest</el-button>
				</div>
				<el-form label-position="top">
					<el-form-item label="Allow this person to view records of this Table">
						<el-radio-group v-model="guest.role.read.allowed" @change="updateRoles(guest)">
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
					</el-form-item>
					<el-form-item label="Allow this person to ADD record to this table">
						<el-radio-group v-model="guest.role.add.allowed" @change="updateRoles(guest)" style="margin-right:30px">
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guest.role.add.approval" :disabled="!guest.role.add.allowed" @change="updateRoles(guest)">You have to <u>approve</u> first, after that the record will be added</el-checkbox>
					</el-form-item>
					<el-form-item label="Allow this person to MODIFY records of this table">
						<el-radio-group v-model="guest.role.update.allowed" @change="updateRoles(guest)" style="margin-right:30px">>
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guest.role.update.approval" :disabled="!guest.role.update.allowed" @change="updateRoles(guest)">You have to <u>approve</u> first, after that the record will be modified</el-checkbox>
					</el-form-item>
					<el-form-item label="Allow this person to REMOVE records from this table">
						<el-radio-group v-model="guest.role.delete.allowed" @change="updateRoles(guest)" style="margin-right:30px">>
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guest.role.delete.approval" :disabled="!guest.role.delete.allowed" @change="updateRoles(guest)">You have to <u>approve</u> first, after that the record will be deleted</el-checkbox>
					</el-form-item>
				</el-form>
			</el-card>
		</el-row>
		<el-row>
			<h3>Manage Administrators
				<el-button style="float: right; padding: 3px 0" type="text" icon="el-icon-circle-plus" @click="addAdmin()">Add Admin</el-button>
			</h3>
			<el-card class="box-card" v-show="admins.length > 0">
				<div v-for="(admin, index) in admins" :key="admin" class="text item">
					<span>Name : {{admin.name}} | Email : {{admin.email}}</span>
					<el-button size="mini" @click="removeAdmin(index, admin)" icon="el-icon-delete" style="float: right; padding: 3px 5px;">Remove</el-button>
				</div>
			</el-card>
		</el-row>
		<el-dialog title="Add Guest" :visible.sync="guestAddDialog">
			<el-row style="height:300px;overflow-y:auto">
				<el-form label-position="top">
					<el-form-item label="Email">
						<el-input v-model="newGuestEmail"></el-input>
					</el-form-item>
					<el-form-item label="Allow this person to view records of this Table">
						<el-radio-group v-model="guestRoleTemp.read.allowed">
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
					</el-form-item>
					<el-form-item label="Allow this person to ADD record to this table">
						<el-radio-group v-model="guestRoleTemp.add.allowed" style="margin-right:30px">
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guestRoleTemp.add.approval" :disabled="!guestRoleTemp.add.allowed">You have to <u>approve</u> first, after that the record will be added</el-checkbox>
					</el-form-item>
					<el-form-item label="Allow this person to MODIFY records of this table">
						<el-radio-group v-model="guestRoleTemp.update.allowed" style="margin-right:30px">>
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guestRoleTemp.update.approval" :disabled="!guestRoleTemp.update.allowed">You have to <u>approve</u> first, after that the record will be modified</el-checkbox>
					</el-form-item>
					<el-form-item label="Allow this person to REMOVE records from this table">
						<el-radio-group v-model="guestRoleTemp.delete.allowed" style="margin-right:30px">>
							<el-radio :label="true">Yes</el-radio>
							<el-radio :label="false">No</el-radio>
						</el-radio-group>
						<el-checkbox v-model="guestRoleTemp.delete.approval" :disabled="!guestRoleTemp.delete.allowed">You have to <u>approve</u> first, after that the record will be deleted</el-checkbox>
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="addGuest()">Add</el-button>
						<el-button @click="guestAddDialog = false">Cancel</el-button>
					</el-form-item>
				</el-form>
			</el-row>
		</el-dialog>

	</el-main>
</template>

<script>
	module.exports = {
		data: function() {
			return {
				tableName: "",
				displayedTableName: "",
				publicRoles: {
					read: {
						allowed: false
					},
					add: {
						allowed: false,
						approval: true,
						loginRequired: true
					},
					update: {
						allowed: false,
						approval: true,
						loginRequired: true
					},
					delete: {
						allow: false,
						approval: true,
						loginRequired: true
					}
				},
				publicRolesTimeout: 0,
				fields: [],
				allowedFields: [],
				allowedFieldsTimeout: 0,
				guestPermissions: [],
				admins: [],
				guestAddDialog: false,
				newGuestEmail: "",
				guestRoleTemp: {
					read: {
						allowed: false
					},
					add: {
						allowed: false,
						approval: true,
						loginRequired: true
					},
					update: {
						allowed: false,
						approval: true,
						loginRequired: true
					},
					delete: {
						allowed: false,
						approval: true,
						loginRequired: true
					}
				}
			};
		},
		created() {
			this.fetchPermissions();
		},
		methods: {
			goBack() {
				this.$router.go(-1);
			},
			updateGlobalFields() {
				clearTimeout(this.allowedFieldsTimeout);
				this.allowedFieldsTimeout = setTimeout(() => {
					for (var field of this.fields) {
						field.isVisible = false;
					}
					for (var allowed of this.allowedFields) {
						for (var field of this.fields) {
							if (field.id == allowed) {
								field.isVisible = true;
							}
						}
					}
					axios.post('/api/table-update-fields.php', {
						'tableName': this.tableName,
						'displayedTableName': this.displayedTableName,
						'fields': this.fields
					}).then(response => {
						if (response.data.status != 'success') {
							this.$message({
								message: 'Unable to update information',
								type: 'error'
							});
							console.error(response.data.message);
						}
					}).catch(error => {
						this.showError('Unable to update information');
					});
				}, 1000);
			},
			updateGlobalRoles() {
				clearTimeout(this.publicRolesTimeout);
				this.publicRolesTimeout = setTimeout(() => {
					axios
						.post("/api/table-update-roles.php", {
							tableName: this.tableName,
							role: this.publicRoles
						})
						.then(result => {
							if (result.data.status != "success") {
								this.$message({
									message: result.data.message,
									type: "error"
								});
							}
						})
						.catch(error => {
							this.$message({
								message: `Problem while updating permissions`,
								type: "error"
							});
						});
				}, 1000);
			},
			updateRoles(guest) {
				axios
					.post("/api/table-update-roles.php", {
						tableName: this.tableName,
						role: guest.role,
						userId: guest.userId
					})
					.then(result => {
						if (result.data.status != "success") {
							this.$message({
								message: result.data.message,
								type: "error"
							});
						}
					})
					.catch(error => {
						this.$message({
							message: `Problem while updating permissions`,
							type: "error"
						});
					});
			},
			removeGuest(guest, index) {
				axios
					.post(`/api/guest-remove.php`, {
						tableName: this.tableName,
						userId: guest.userId
					})
					.then(result => {
						if (result.data.status == "success") {
							this.$message({
								message: "Removed",
								type: "success"
							});
							this.guestPermissions.splice(index, 1);
						} else {
							this.$message({
								message: result.data.message,
								type: "error"
							});
						}
					})
					.catch(error => {
						console.log(error);
						this.$message({
							message: `Problem while removing ${guest.email} from guests list`,
							type: "error"
						});
					});
			},
			addGuest() {
				axios
					.post(`/api/guest-add.php`, {
						email: this.newGuestEmail,
						tableName: this.tableName,
						role: this.guestRoleTemp
					})
					.then(result => {
						if (result.data.status == "success") {
							this.$message({
								message: "Added",
								type: "success"
							});
							this.newGuestEmail = "";
							this.guestAddDialog = false;
							axios
								.get(`/api/table-permissions.php?tableName=${this.tableName}`)
								.then(result => {
									this.guestPermissions = result.data.guestPermissions;
								})
								.catch(error => {
									this.$message({
										message: "Unable to update table information",
										type: "error"
									});
								});
						} else {
							this.$message({
								message: result.data.message,
								type: "error"
							});
						}
					})
					.catch(error => {
						this.$message({
							message: `Problem while adding ${this.newGuestEmail} to Guests`,
							type: "error"
						});
					});
			},
			addAdmin() {
				this.$prompt("Please input e-mail", "Add Administrator", {
						confirmButtonText: "OK",
						cancelButtonText: "Cancel"
					})
					.then((value) => {

						var email = value.value;
						axios
							.post(`/api/admin-add.php`, {
								email: email,
								tableName: this.tableName
							})
							.then(result => {
								if (result.data.status == "success") {
									this.$message({
										message: "Added",
										type: "success"
									});
									axios
										.get(`/api/table-permissions.php?tableName=${this.tableName}`)
										.then(result => {
											this.admins = result.data.admins;
										})
										.catch(error => {
											this.$message({
												message: "Unable to update table information",
												type: "error"
											});
										});
								} else {
									this.$message({
										message: result.data.message,
										type: "error"
									});
								}
							})
							.catch(error => {
								this.$message({
									message: `Problem while adding ${email} to admins list`,
									type: "error"
								});
							});
					})
					.catch(error => {
						console.log(error);
					});
			},
			removeAdmin(index, admin) {
				this.$confirm(
						`Are you sure want to remove ${admin.email} from admin list' ?`,
						"Warning", {
							confirmButtonText: "OK",
							cancelButtonText: "Cancel",
							type: "warning"
						}
					)
					.then(() => {
						axios
							.post(`/api/admin-remove.php`, {
								userId: admin.userId,
								tableName: this.tableName
							})
							.then(result => {
								if (result.data.status == "success") {
									this.$message({
										message: "Removed",
										type: "success"
									});
									this.admins.splice(index, 1);
								} else {
									this.$message({
										message: result.data.message,
										type: "error"
									});
								}
							})
							.catch(error => {
								console.log(error);
								this.$message({
									message: `Problem while removing ${admin.email} from admins list`,
									type: "error"
								});
							});
					})
					.catch(() => {
						this.$message({
							message: `Problem while removing ${row.email} from admins list`,
							type: "error"
						});
					});
			},
			fetchPermissions() {
				this.tableName = this.$route.params.tableName;
				axios
					.get(`/api/table-permissions.php?tableName=${this.tableName}`)
					.then(result => {
						this.displayedTableName = result.data.displayedTableName;
						this.fields = result.data.fields;
						this.guestPermissions = result.data.guestPermissions;
						this.admins = result.data.admins;
						this.publicRoles = result.data.publicRoles;
						if (this.publicRoles.read.allowed) {
							for (var field of this.fields) {
								if (field.isVisible) {
									this.allowedFields.push(field.id);
								}
							}
						}
					})
					.catch(error => {
						console.log(error);
						this.$message({
							message: "Unable to fetch table information",
							type: "error"
						});
					});
			}
		}
	};
</script>
