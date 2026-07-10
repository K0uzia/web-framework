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

interface Cta13Props extends CtaSimpleProps {}
type Props = Partial<Cta13Props>;

const defaultProps: Cta13Props = {
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

const Cta13 = (props: Props) => {
  const { heading, description, buttons, className } = {
    ...defaultProps,
    ...props,
  };

  return (
    <section className={cn("py-32", className)}>
      <div className="container mx-auto">
        <div className="mx-auto max-w-5xl rounded-lg bg-accent p-8 md:rounded-xl lg:p-12">
          <div className="flex flex-col gap-4 lg:gap-6">
            <h2 className="text-2xl font-semibold tracking-tight md:text-4xl">
              {heading}
            </h2>
            <p className="text-muted-foreground lg:text-lg">{description}</p>
            <div className="mt-4 flex flex-col gap-3 sm:flex-row sm:gap-4">
              {buttons?.primary && (
                <Button size="lg" className="w-full sm:w-auto" render={<a href={buttons.primary.url} />} nativeButton={false}>{buttons.primary.text}</Button>
              )}
              {buttons?.secondary && (
                <Button variant="outline" size="lg" className="w-full sm:w-auto" render={<a href={buttons.secondary.url} />} nativeButton={false}>{buttons.secondary.text}</Button>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export { Cta13 };
