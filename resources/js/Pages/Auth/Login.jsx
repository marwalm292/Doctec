import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Login({ status, canResetPassword }) {
    const [debugInfo, setDebugInfo] = useState(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const { auth } = usePage().props;

    useEffect(() => {
        // Check if user is authenticated
        if (auth?.user) {
            console.log("Auth user detected:", auth.user);
            
            // Check if role is an object (enum) or a string
            const userRole = auth.user.role;
            const isRoleObject = typeof userRole === 'object' && userRole !== null;
            
            console.log("User role:", userRole);
            
            // Handle both object and string roles
            let isAdmin = false;
            
            if (isRoleObject && userRole.value === 'admin') {
                isAdmin = true;
                console.log("Admin detected via enum value");
            } else if (userRole === 'admin') {
                isAdmin = true;
                console.log("Admin detected via direct string comparison");
            }
            
            console.log("Is admin?", isAdmin);
            
            // Redirect based on role
            if (isAdmin) {
                console.log("Redirecting to admin dashboard");
                router.visit(route('admin.dashboard'));
            } else {
                console.log("Redirecting to user dashboard");
                router.visit(route('dashboard'));
            }
        }
    }, [auth]);

    const submit = (e) => {
        e.preventDefault();
        console.log("Submitting login form with data:", data);

        post(route('login'), {
            onSuccess: (page) => {
                console.log("Login successful, response:", page);
                reset('password');
                
                // Check if we have user info and can redirect
                if (page.props.auth && page.props.auth.user) {
                    const user = page.props.auth.user;
                    console.log("User logged in:", user);
                    
                    // Handle role comparison for both string and object roles
                    const userRole = user.role;
                    const isRoleObject = typeof userRole === 'object' && userRole !== null;
                    
                    let isAdmin = false;
                    if (isRoleObject && userRole.value === 'admin') {
                        isAdmin = true;
                    } else if (userRole === 'admin') {
                        isAdmin = true;
                    }
                    
                    setDebugInfo({
                        user: user.email,
                        role: isRoleObject ? userRole.value : userRole,
                        isAdmin
                    });
                    
                    if (isAdmin) {
                        console.log("Admin user detected, redirecting to admin dashboard");
                        router.visit(route('admin.dashboard'));
                    } else {
                        console.log("Regular user detected, redirecting to dashboard");
                        router.visit(route('dashboard'));
                    }
                }
            },
            onError: (errors) => {
                console.error("Login errors:", errors);
                setDebugInfo({ errors });
            }
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
            
            {debugInfo && (
                <div className="mb-4 text-sm font-medium text-gray-600 bg-gray-100 p-4 rounded">
                    <pre>{JSON.stringify(debugInfo, null, 2)}</pre>
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span className="ms-2 text-sm text-gray-600">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Forgot your password?
                        </Link>
                    )}

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Log in
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
