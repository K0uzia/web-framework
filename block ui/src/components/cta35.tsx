import { Button } from "@/components/ui/button";

import { cn } from "@/lib/utils";

interface Button {
  text: string;
  url: string;
  icon?: React.ReactNode;
}
interface Buttons {
  primary?: Button;
  secondary?: Button;
}

interface CtaSimpleProps {
  heading: string;
  description: string;
  buttons?: Buttons;
  className?: string;
}

interface Cta35Props extends CtaSimpleProps {}
type Props = Partial<Cta35Props>;

const defaultProps: Cta35Props = {
  heading: "Call to Action",
  description:
    "Get access to our collection of pre-built blocks and components today.",
  buttons: {
    primary: {
      text: "Get Access",
      url: "https://shadcnblocks.com",
    },
    secondary: {
      text: "Schedule a Demo",
      url: "https://shadcnblocks.com",
    },
  },
};

const Cta35 = (props: Props) => {
  const { heading, description, buttons, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container mx-auto">
        <div className="flex max-w-5xl flex-col gap-4 border-l-4 border-primary pl-8 lg:gap-6">
          <h2 className="text-2xl font-semibold tracking-tight md:text-4xl">
            {heading}
          </h2>
          <p className="max-w-xl text-muted-foreground lg:text-lg">
            {description}
          </p>
          <div className="flex flex-col gap-3 sm:flex-row">
            {buttons?.primary && (
              <Button size="lg" render={<a href={buttons.primary.url} />} nativeButton={false}>{buttons.primary.text}</Button>
            )}
            {buttons?.secondary && (
              <Button variant="outline" size="lg" render={<a href={buttons.secondary.url} />} nativeButton={false}>{buttons.secondary.text}</Button>
            )}
          </div>
        </div>
      </div>
    </section>
  );
};

export { Cta35 };
