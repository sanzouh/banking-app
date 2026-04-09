import {
  Anchor,
  Button,
  Checkbox,
  Container,
  Group,
  Paper,
  PasswordInput,
  Text,
  TextInput,
  Title,
} from '@mantine/core';
import classes from './AuthenticationTitle.module.css';
import { useForm } from '@mantine/form';
import { Link } from 'react-router-dom';

export default function Register() {
const form = useForm({
    mode: 'uncontrolled',
    initialValues: {
      name: '',
      email: '',
      password: '',
    },

    validate: {
      name: (value) => (value.length >= 2 ? null : 'Name must be at least 2 characters'),
      email: (value) => (/^\S+@\S+$/.test(value) ? null : 'Invalid email'),
      password: (value) => (value.length >= 6 ? null : 'Password must be at least 6 characters'),
    },
  });

  return (
    <Container size={420} my={40}>
      <Title ta="center" className={classes.title}>
        Welcome!
      </Title>

      <Text className={classes.subtitle}>
        Already have an account? <Anchor component={Link} to="/" >Login</Anchor>
      </Text>

      <Paper withBorder shadow="sm" p={22} mt={30} radius="md">
        <TextInput label="Name" placeholder="Your name" required radius="md" {...form.getInputProps('name')} />
        <TextInput label="Email" placeholder="you@jasby.dev" required radius="md" {...form.getInputProps('email')} />
        <PasswordInput label="Password" placeholder="Your password" required mt="md" radius="md" {...form.getInputProps('password')} />

        <Group justify="space-between" mt="lg">
          <Checkbox label="Remember me" />
        </Group>

        <Button fullWidth mt="xl" radius="md" onClick={() => form.validate()}>
          Sign in
        </Button>
      </Paper>
    </Container>
  );
}