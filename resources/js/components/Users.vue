<template>
    <div class="container" v-if="$gate.isAdmin() || $gate.isAuthor()">
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Users Table</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-success" @click="newModal">Add New
                                <i class="fa fa-user fa-fw"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tbody><tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Registered At</th>
                                <th>Actions</th>
                            </tr>
                            <template v-if="users.data">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>{{ user.id }}</td>
                                    <td>{{ user.name | upText }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.type | upText }}</td>
                                    <td>{{ user.created_at | myDate }}</td>
                                    <td>
                                        <a href="" @click.prevent="editModal(user)">
                                            <i class="fa fa-edit blue"></i>
                                        </a>
                                        /
                                        <a href="" @click.prevent="deleteUser(user.id)">
                                            <i class="fa fa-trash red"></i>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                        <pagination class="justify-content-center" :data="users" @pagination-change-page="fetchUsers"></pagination>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addNewModal" tabindex="-1" role="dialog" aria-labelledby="addNewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addNewModalLabel">{{ editMode ? 'Edit User' : 'Add New' }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form @submit.prevent="editMode ? updateUser() : createUser()" @keydown="form.errors.clear($event.target.name)">
                        <div class="modal-body">
                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name" placeholder="Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.email" type="email" name="email" placeholder="Email Address"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>
                            <div class="form-group">
                                <select class="form-control" name="type" v-model="form.type">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>
                            <div class="form-group">
                                <input v-model="form.password" type="password" name="password" placeholder="Password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button type="submit" v-show="!editMode" class="btn btn-success">Create</button>
                            <button type="submit" v-show="editMode" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <not-found v-else></not-found>
</template>

<script>
    export default {
        data() {
            return {
                editMode: false,
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    password: '',
                    type: ''
                }),
                users: {},
            }
        },
        methods: {
            newModal() {
                this.editMode = false;
                this.form.reset();
                this.form.clear();
                $('#addNewModal').modal();
            },
            editModal(user) {
                this.editMode = true;
                this.form.reset();
                this.form.clear();
                $('#addNewModal').modal();
                this.form.fill(user);
            },
            fetchUsers(page = 1) {
                if (!(this.$gate.isAdmin() || this.$gate.isAuthor()))
                    return;

                let search = this.$parent.searchField;
                axios.get(`api/user?${search ? `search=${search}&` : ''}page=${page}`)
                    .then(({ data }) => this.users = data)
                    .catch(response => console.log(response.data))
            },
            createUser() {
                this.$Progress.start();
                this.form.post('api/user')
                    .then(() => {
                        $('#addNewModal').modal('hide');
                        Toast.fire({
                            type: 'success',
                            title: 'Created successfully'
                        });
                        this.$Progress.finish();
                        VueEvent.$emit('userCreated');
                    })
                    .catch(() => {
                        this.$Progress.fail();
                        console.log('something went wrong when creating user');
                    });
            },
            updateUser() {
                this.$Progress.start();
                this.form.put(`api/user/${this.form.id}`)
                    .then(() => {
                        $('#addNewModal').modal('hide');
                        Toast.fire({
                            type: 'success',
                            title: 'Updated successfully'
                        });
                        this.$Progress.finish();
                        VueEvent.$emit('userUpdated');
                    })
                    .catch(() => {
                        this.$Progress.fail();
                        console.log('something went wrong when updating user');
                    })
            },
            deleteUser(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        // send request to server to delete user
                        axios.delete(`api/user/${id}`)
                            .then(() => {
                                Swal.fire(
                                    'Deleted!',
                                    'User has been deleted.',
                                    'success'
                                );
                                VueEvent.$emit('userDeleted');
                            })
                            .catch(() => Swal.fire({
                                type: 'error',
                                title: 'Oops...',
                                text: 'something went wrong when deleting user'
                            }));
                    }
                })
            }
        },
        created() {
            this.fetchUsers();
            VueEvent.$on('userCreated', this.fetchUsers);
            VueEvent.$on('userDeleted', this.fetchUsers);
            VueEvent.$on('userUpdated', this.fetchUsers);
            VueEvent.$on('searching', this.fetchUsers);
        }
    }
</script>
